# Testes

| Camada                      | Ferramenta                             | Local                | Comando                                    |
| --------------------------- | -------------------------------------- | -------------------- | ------------------------------------------ |
| API (feature)               | PHPUnit                                | `tests/Feature/Api`  | `docker compose exec app php artisan test` |
| Regras (unit)               | PHPUnit                                | `tests/Unit`         | idem                                       |
| Frontend (unit/componentes) | Vitest + Vue Test Utils (jsdom)        | `resources/js/tests` | `npm test`                                 |
| E2E                         | Playwright (Chromium)                  | `e2e/`               | `npm run test:e2e`                         |
| Estática                    | PHPStan nível 7, vue-tsc, Oxlint, Pint | —                    | ver README                                 |

## Backend (49 testes)

Rodam em **SQLite em memória** (`phpunit.xml`), inclusive a busca por distância (mesma SQL de produção).

- `AuthTest` — cadastro dos dois perfis, termos obrigatórios, login por sessão (SPA) e por token, bloqueio após 5 tentativas, logout, senha nunca exposta.
- `AccountTest` — atualização de dados e senha, upload de avatar válido, rejeição de arquivo falso/SVG, exclusão de conta (LGPD).
- `ProfileTest` — atualização/normalização do perfil da ONG, bloqueio de edição de terceiros, filtro de ONGs por raio, consentimento de localização (salvar, descartar, revogar), privacidade do perfil do voluntário.
- `HelpRequestTest` — criação herdando localização, validação, busca por texto/tipo/prioridade, distância e ordenação, visibilidade por status e ONG ativa, `mine`, edição por dono, bloqueio de estados finais, conclusão em cascata, excluir vs. cancelar, categorias.
- `InterestFlowTest` — fluxo completo (interesse → análise → aprovação com contato liberado → conclusão → histórico), notificações, duplicidade, reativação, bloqueios de perfil, transições inválidas, dashboards.
- `StatusTransitionsTest` — máquina de estados dos enums.

## Frontend (30 testes)

- `format`, `geo` — formatação pt-BR, máscaras e Haversine.
- `http` — normalização de erros (422, 5xx sem vazamento, rede).
- `useForm`, `useAsync` — mapeamento de erros 422, reset, descarte de respostas antigas.
- `authStore` — deduplicação de `/me`, visitante, logout resiliente.
- `guards` — redirecionamentos por autenticação e perfil.
- `components` — `StatusBadge`, `FormField` (atributos ARIA), `RequestCard`.

## E2E (6 testes)

Pré-requisito: stack rodando e banco migrado.

```bash
docker compose up -d
docker compose exec app php artisan migrate:fresh --seed
npm run build
npx playwright install chromium   # primeira vez
npm run test:e2e
```

- `public.spec` — home → cadastro, redirecionamento de rota protegida, _skip link_ como primeiro foco, erro de login acessível.
- `interest-flow.spec` — **fluxo completo do MVP** com dois usuários reais (ONG publica → voluntário pesquisa e demonstra interesse → ONG vê interessado sem contato, aprova e vê contato → ONG conclui → voluntário vê "Concluído" no histórico) e bloqueio de telas de ONG para voluntários.
