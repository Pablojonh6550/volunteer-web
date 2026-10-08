# Arquitetura

## Visão geral

```
Vue 3 (SPA) ──HTTP/JSON──► Laravel API ──► Actions / Services ──► Eloquent ──► MySQL
   │  cookie de sessão (Sanctum)   │                                   │
   │                               ├── Policies (autorização)          └──► Redis (cache)
   │                               ├── Form Requests (validação)
   │                               └── Notifications ──► Queue (Redis) ──► worker
```

- O **Laravel** é responsável por API, autenticação, autorização, validação, regras de negócio e persistência.
- O **Vue 3** é um SPA independente que consome apenas os endpoints `/api/*`. O Laravel serve somente o HTML de entrada (`resources/views/app.blade.php`) para qualquer rota fora da API (`routes/web.php`), e o Vue Router assume a navegação.
- Frontend e backend compartilham o mesmo domínio, o que permite a autenticação stateful do Sanctum (cookie HttpOnly + CSRF), sem expor tokens ao JavaScript.

## Estrutura do backend

```
app/
├── Actions/                 Regras de negócio com efeitos (1 classe = 1 caso de uso)
│   ├── Auth/RegisterUser
│   ├── Participations/      ExpressInterest, CancelInterest, ReviewParticipation
│   ├── Requests/            CreateHelpRequest, UpdateHelpRequest, DeleteHelpRequest
│   └── Volunteers/          UpdateVolunteerProfile (inclui consentimento LGPD)
├── Enums/                   UserType, RequestType, RequestStatus, RequestPriority,
│                            RequestVolunteerStatus, OrganizationStatus
├── Exceptions/              BusinessRuleException (→ HTTP 422)
├── Http/
│   ├── Controllers/Api/     Controllers finos: autorizam, chamam Action/Service, devolvem Resource
│   ├── Middleware/          EnsureUserType (alias `user.type`)
│   ├── Requests/            Form Requests (validação + normalização)
│   └── Resources/           Formato JSON e controle de exposição de dados
├── Models/                  Eloquent + Concerns/HasCoordinates (busca geográfica)
├── Notifications/           E-mails assíncronos (fila)
├── Policies/                Autorização por recurso
└── Services/                Consultas compostas: HelpRequestSearch, OrganizationSearch, DashboardService
routes/api.php               Endpoints da API
```

### Responsabilidades

| Camada       | Faz                                                                          | Não faz                              |
| ------------ | ---------------------------------------------------------------------------- | ------------------------------------ |
| Controller   | Orquestração HTTP: `Gate::authorize`, chama Action/Service, retorna Resource | Regras de negócio, queries complexas |
| Form Request | Valida e normaliza entrada (CEP, CNPJ, UF)                                   | Autorização por recurso              |
| Policy       | Decide _quem_ pode fazer _o quê_ em cada recurso                             | Validação de dados                   |
| Action       | Um caso de uso com regras (transições de status, cascatas, notificações)     | Formatar resposta                    |
| Service      | Consultas de leitura reutilizáveis (pesquisa, dashboard)                     | Alterar estado                       |
| Resource     | Monta o JSON e esconde dados pessoais conforme quem pede                     | Buscar dados                         |

> Seguindo o guia, **evitamos abstrações sem necessidade**: não há camada de Repository; atualizações simples (ex.: dados da ONG) ficam no controller com `$model->update($validated)`.

## Modelagem do banco

```
users 1───1 organizations 1───N requests N───1 categories
  │                                  │
  1                                  N
  │                                  │
volunteers 1───────────N request_volunteers
  │
  N───N categories  (category_volunteer: áreas de interesse)
```

| Tabela               | Campos principais                                                                                                             |
| -------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `users`              | id, name, email, password, **type**, phone, avatar, timestamps                                                                |
| `organizations`      | user_id (único), name, document (CNPJ), description, phone, email, website, endereço, latitude, longitude, status             |
| `volunteers`         | user_id (único), birth_date, phone, bio, endereço, latitude, longitude, **location_consent_at**, available                    |
| `categories`         | name, type, active (único por nome+tipo)                                                                                      |
| `category_volunteer` | category_id, volunteer_id                                                                                                     |
| `requests`           | organization_id, category_id, type, title, description, quantity, priority, status, start_date, end_date, latitude, longitude |
| `request_volunteers` | request_id, volunteer_id (único por par), status, message                                                                     |

Diferenças em relação à modelagem inicial do guia (e motivo):

- `volunteers.location_consent_at` — registro do consentimento para uso da localização (LGPD).
- `category_volunteer` — o guia pede que o voluntário informe "áreas de interesse".
- O model da tabela `requests` chama-se **`HelpRequest`** para não conflitar com `Illuminate\Http\Request`. A API e o banco mantêm o termo _request_.
- Índices compostos `(latitude, longitude)` para o pré-filtro geográfico e `(status, type)` para a pesquisa.

## Enums e ciclos de vida

**RequestType**: `donation`, `service`, `volunteering`
**RequestPriority**: `low`, `medium`, `high`, `urgent`

**RequestStatus**

```
open ──► in_progress ──► completed
  │           │
  └───────────┴────────► cancelled        (completed e cancelled são finais)
```

**RequestVolunteerStatus** (fluxo de interesse)

```
interested ──► approved ──► completed
     │             │
     ├──► rejected ◄┘
     └──► cancelled   (pelo voluntário, enquanto interested/approved)
```

As transições permitidas estão codificadas nos próprios enums (`allowedTransitions()`, `organizationTransitions()`), e as Actions lançam `BusinessRuleException` (HTTP 422) para transições inválidas.

## Regras de negócio

| Regra                                                                                               | Onde                                     |
| --------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| Cadastro cria automaticamente o perfil (ONG ou voluntário)                                          | `RegisterUser`                           |
| Solicitação sem localização herda a da ONG                                                          | `CreateHelpRequest`                      |
| Solicitações concluídas/canceladas não podem ser editadas                                           | `UpdateHelpRequest`                      |
| Concluir solicitação: aprovados → `completed`; pendentes → `rejected`                               | `UpdateHelpRequest`                      |
| Cancelar solicitação: participações ativas → `cancelled`                                            | `UpdateHelpRequest`                      |
| DELETE exclui se não houver voluntários; senão cancela (preserva histórico)                         | `DeleteHelpRequest`                      |
| Interesse só em solicitações `open`/`in_progress`; um vínculo por voluntário (reativa se cancelado) | `ExpressInterest`                        |
| Aprovar o primeiro voluntário move a solicitação `open` → `in_progress`                             | `ReviewParticipation`                    |
| ONG e voluntário são notificados por e-mail (fila, após commit)                                     | `Notifications/*`                        |
| Voluntários só veem solicitações abertas/em andamento de ONGs ativas                                | `HelpRequestSearch`, `HelpRequestPolicy` |
| Localização do voluntário só é salva com consentimento; revogar apaga coordenadas                   | `UpdateVolunteerProfile`                 |

## Busca por proximidade

`App\Models\Concerns\HasCoordinates` adiciona dois scopes aos models com latitude/longitude:

- `withDistanceFrom($lat, $lng)` — adiciona a coluna calculada `distance_km` (fórmula de **Haversine** em SQL).
- `nearby($lat, $lng, $radiusKm)` — aplica primeiro um _bounding box_ (usa o índice) e depois o filtro exato por distância.

O cálculo acontece no banco, então filtro, ordenação e paginação continuam em SQL. As funções usadas (`ASIN`, `SQRT`, `POWER`, `SIN`, `COS`, `RADIANS`) existem em MySQL 8 e SQLite, então os testes (SQLite em memória) exercitam a mesma query de produção.

## Autorização

Duas camadas complementares:

1. **Middleware `user.type`** — bloqueia rotas inteiras por perfil (ex.: `/api/me/activities` só para voluntários).
2. **Policies** — decidem por recurso:

| Policy                   | Regras                                                                                                                                    |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `OrganizationPolicy`     | Qualquer autenticado vê ONGs ativas; só o dono edita                                                                                      |
| `VolunteerPolicy`        | O próprio voluntário ou ONGs em cujas solicitações ele demonstrou interesse                                                               |
| `HelpRequestPolicy`      | Criar: só ONG · editar/excluir/ver interessados: ONG dona · interesse: só voluntário · ver: pública se aberta, senão dona ou participante |
| `RequestVolunteerPolicy` | Só a ONG dona da solicitação aprova/recusa/conclui                                                                                        |
