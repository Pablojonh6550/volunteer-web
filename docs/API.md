# API — referência

Base: `/api` · Formato: JSON · Datas: ISO 8601 · Coordenadas: graus decimais.

## Convenções

- Envie `Accept: application/json`.
- Recursos vêm envelopados em `data`. Listas paginadas incluem `links` e `meta` (`current_page`, `last_page`, `per_page`, `total`).
- **Erros**

| Status | Quando                                   | Corpo                                                  |
| ------ | ---------------------------------------- | ------------------------------------------------------ |
| 401    | Não autenticado                          | `{ "message": "Unauthenticated." }`                    |
| 403    | Sem permissão (Policy / tipo de usuário) | `{ "message": "..." }`                                 |
| 404    | Recurso inexistente                      | `{ "message": "..." }`                                 |
| 419    | Token CSRF expirado (SPA)                | —                                                      |
| 422    | Validação **ou** regra de negócio        | `{ "message": "...", "errors": { "campo": ["..."] } }` |
| 429    | Rate limit excedido                      | `{ "message": "Too Many Attempts." }`                  |

## Autenticação

### Modo SPA (usado pelo frontend)

1. `GET /sanctum/csrf-cookie` — define o cookie `XSRF-TOKEN`.
2. `POST /api/auth/login` com o header `X-XSRF-TOKEN` (o axios faz isso automaticamente).
3. A sessão fica em cookie HttpOnly. Nenhum token é devolvido.

### Modo token (Postman, apps)

Requisições que não vêm de um domínio stateful recebem `token` na resposta de login/cadastro. Use `Authorization: Bearer <token>`. `POST /api/auth/logout` revoga o token atual.

### Rate limiting

| Limitador        | Rotas                           | Limite                                    |
| ---------------- | ------------------------------- | ----------------------------------------- |
| `auth`           | `/auth/register`, `/auth/login` | 10/min por IP                             |
| login (bloqueio) | `/auth/login`                   | 5 falhas por e-mail+IP → bloqueio de 60 s |
| `api`            | demais rotas                    | 120/min por usuário                       |
| `uploads`        | `/me/avatar`                    | 10/min por usuário                        |

---

## Endpoints

### Autenticação e conta

| Método | Endpoint         | Descrição                                               |
| ------ | ---------------- | ------------------------------------------------------- |
| POST   | `/auth/register` | Cadastro (cria usuário + perfil)                        |
| POST   | `/auth/login`    | Login                                                   |
| POST   | `/auth/logout`   | Logout                                                  |
| GET    | `/me`            | Usuário autenticado (com `organization` ou `volunteer`) |
| PUT    | `/me`            | Atualiza nome, e-mail, telefone                         |
| PUT    | `/me/password`   | Troca de senha                                          |
| POST   | `/me/avatar`     | Upload de avatar (multipart)                            |
| DELETE | `/me/avatar`     | Remove avatar                                           |
| DELETE | `/me`            | Exclui a conta e os dados (LGPD)                        |
| GET    | `/me/activities` | _(voluntário)_ Minhas atividades                        |
| GET    | `/dashboard`     | Indicadores do perfil logado                            |
| GET    | `/categories`    | Categorias ativas (cache)                               |

**POST /auth/register**

```json
{
    "name": "Ana Souza",
    "email": "ana@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "type": "volunteer", // ou "organization"
    "organization_name": "Instituto X", // obrigatório se type=organization
    "phone": "11999999999",
    "terms": true // aceite obrigatório (LGPD)
}
```

Resposta `201`: `{ "data": User, "token"?: string }`.

**POST /auth/login** — `{ "email", "password", "remember"?: bool }` → `200 { "data": User, "token"? }`.

**PUT /me/password** — `{ "current_password", "password", "password_confirmation" }` → `204`.

**POST /me/avatar** — `multipart/form-data` com `avatar` (JPG/PNG/WebP, ≤ 2 MB, ≤ 4000×4000) → `200 { "data": User }`.

**DELETE /me** — `{ "password" }` → `204`.

**GET /me/activities** — `?status=interested|approved|rejected|cancelled|completed&page=` → lista paginada de `RequestVolunteer` com `request.organization`.

**GET /dashboard**

ONG:

```json
{
    "data": {
        "requests_by_status": {
            "open": 3,
            "in_progress": 1,
            "completed": 2,
            "cancelled": 0
        },
        "pending_interests": 4,
        "approved_volunteers": 5,
        "profile_complete": true
    }
}
```

Voluntário:

```json
{
    "data": {
        "participations_by_status": {
            "interested": 1,
            "approved": 1,
            "rejected": 0,
            "cancelled": 0,
            "completed": 3
        },
        "open_requests": 22,
        "nearby_requests": 8, // null sem localização
        "profile_complete": true
    }
}
```

### ONGs

| Método | Endpoint              | Descrição                       |
| ------ | --------------------- | ------------------------------- |
| GET    | `/organizations`      | Lista ONGs ativas (mapa)        |
| GET    | `/organizations/{id}` | Detalhes + solicitações abertas |
| PUT    | `/organizations/{id}` | Atualiza (somente o dono)       |

**GET /organizations** — filtros: `q`, `city`, `lat`, `lng`, `radius` (km, 1–200), `per_page`. Com `lat/lng` cada item traz `distance_km` e a lista vem ordenada pela distância. Cada item traz `open_requests_count`.

**PUT /organizations/{id}**

```json
{
    "name": "Instituto X",
    "document": "12.345.678/0001-90", // normalizado para dígitos
    "description": "...",
    "phone": "...",
    "email": "...",
    "website": "https://...",
    "address": "...",
    "number": "...",
    "neighborhood": "...",
    "city": "...",
    "state": "sp", // normalizado para "SP"
    "zip_code": "01310-100", // normalizado para "01310100"
    "latitude": -23.56,
    "longitude": -46.65
}
```

### Voluntários

| Método | Endpoint           | Descrição                                             |
| ------ | ------------------ | ----------------------------------------------------- |
| GET    | `/volunteers/{id}` | Perfil (próprio: completo · ONG relacionada: público) |
| PUT    | `/volunteers/{id}` | Atualiza o próprio perfil                             |

**PUT /volunteers/{id}**

```json
{
    "birth_date": "1995-04-10", // idade mínima 16 anos
    "phone": "...",
    "bio": "...",
    "available": true,
    "category_ids": [1, 5, 9], // áreas de interesse
    "location_consent": true, // sem consentimento, lat/lng são descartadas
    "latitude": -23.55,
    "longitude": -46.63,
    "address": "...",
    "city": "...",
    "state": "SP",
    "zip_code": "..."
}
```

Perfil público (visto por ONGs): `id, name, avatar_url, bio, city, state, available, categories`. Endereço, data de nascimento, telefone e coordenadas **nunca** são expostos a terceiros.

### Solicitações

| Método    | Endpoint         | Descrição                                          |
| --------- | ---------------- | -------------------------------------------------- |
| GET       | `/requests`      | Pesquisar                                          |
| POST      | `/requests`      | Criar _(ONG)_                                      |
| GET       | `/requests/{id}` | Detalhes                                           |
| PUT/PATCH | `/requests/{id}` | Atualizar dados e/ou status _(ONG dona)_           |
| DELETE    | `/requests/{id}` | Excluir (sem voluntários) ou cancelar _(ONG dona)_ |

**GET /requests** — filtros:

| Parâmetro          | Tipo                                 | Observação                                 |
| ------------------ | ------------------------------------ | ------------------------------------------ |
| `q`                | string                               | Busca em título e descrição                |
| `type`             | RequestType                          |                                            |
| `category_id`      | int                                  |                                            |
| `priority`         | RequestPriority                      |                                            |
| `status`           | RequestStatus                        | Voluntários: apenas `open`/`in_progress`   |
| `organization_id`  | int                                  |                                            |
| `mine`             | bool                                 | _(ONG)_ só as próprias, em qualquer status |
| `lat`, `lng`       | float                                | Adiciona `distance_km`                     |
| `radius`           | float (km)                           | Exige `lat`/`lng`                          |
| `sort`             | `recent` \| `distance` \| `priority` | Padrão `recent`                            |
| `per_page`, `page` | int                                  | Máx. 50 por página                         |

**POST /requests**

```json
{
    "type": "donation",
    "category_id": 3, // deve pertencer ao mesmo tipo
    "title": "Arrecadação de alimentos", // 5–255
    "description": "Precisamos de cestas...", // 20–5000
    "quantity": 50,
    "priority": "high",
    "start_date": "2026-10-10",
    "end_date": "2026-10-30", // ≥ start_date
    "latitude": null,
    "longitude": null // null → herda da ONG
}
```

Resposta `201 { "data": HelpRequest }`.

**PUT /requests/{id}** — mesmos campos (todos opcionais) + `status`. Transições inválidas → `422`.

**DELETE /requests/{id}**

```json
{ "deleted": false, "message": "A solicitação possui voluntários vinculados e foi cancelada...", "data": HelpRequest }
```

**HelpRequest**

```json
{
  "id": 1, "organization_id": 1, "category_id": 3,
  "type": "donation", "title": "...", "description": "...",
  "quantity": 50, "priority": "high", "status": "open",
  "start_date": "2026-10-10", "end_date": "2026-10-30",
  "latitude": -23.55, "longitude": -46.63,
  "distance_km": 1.24,          // quando lat/lng informados
  "interested_count": 3,        // interessados + aprovados
  "organization": Organization, "category": Category,
  "my_participation": RequestVolunteer | null,   // GET /requests/{id} para voluntários
  "created_at": "...", "updated_at": "..."
}
```

### Fluxo de interesse

| Método | Endpoint                    | Descrição                                                             |
| ------ | --------------------------- | --------------------------------------------------------------------- |
| POST   | `/requests/{id}/interest`   | _(voluntário)_ Demonstra interesse — `{ "message"?: string }` → `201` |
| DELETE | `/requests/{id}/interest`   | _(voluntário)_ Cancela o interesse                                    |
| GET    | `/requests/{id}/volunteers` | _(ONG dona)_ Interessados — `?status=`                                |
| PATCH  | `/request-volunteers/{id}`  | _(ONG dona)_ `{ "status": "approved" \| "rejected" \| "completed" }`  |

**RequestVolunteer**

```json
{
  "id": 10, "request_id": 1, "volunteer_id": 4,
  "status": "approved", "message": "Posso ajudar aos sábados.",
  "volunteer": Volunteer,                 // perfil público
  "contact": { "email": "...", "phone": "..." },  // só para a ONG, após aprovação
  "created_at": "...", "updated_at": "..."
}
```
