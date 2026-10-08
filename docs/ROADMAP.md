# Roteiro de desenvolvimento

| Sprint             | Escopo do guia                                                     | Status | Principais entregas                                          |
| ------------------ | ------------------------------------------------------------------ | ------ | ------------------------------------------------------------ |
| 1 — Setup          | PHP 8.4, Laravel, MySQL, Docker, Vue 3, TS, Vite, estrutura da API | ✅     | Docker Compose, SPA Vue 3 + Router + Pinia, `routes/api.php` |
| 2 — Autenticação   | Cadastro, login, logout, Sanctum, perfis, autorização por tipo     | ✅     | `AuthController`, Sanctum SPA/token, `user.type`, guards     |
| 3 — Perfis         | CRUD de ONG e voluntário, endereço, localização                    | ✅     | Perfis, `AddressFields`, `LocationPicker`, consentimento     |
| 4 — Solicitações   | Categorias, CRUD, filtros, status, regras de negócio               | ✅     | `HelpRequest*`, enums com transições, `HelpRequestSearch`    |
| 5 — Voluntário     | Dashboard, mapa, pesquisa, detalhes, interesse                     | ✅     | Páginas `volunteer/*`, `useSearch`, `ExpressInterest`        |
| 6 — ONG            | Dashboard, gestão de solicitações, aprovação/recusa                | ✅     | Páginas `organization/*`, `ReviewParticipation`              |
| 7 — Geolocalização | Mapa, distância, ONGs e solicitações próximas                      | ✅     | `HasCoordinates` (Haversine SQL), `useMap`, raio de busca    |
| 8 — Qualidade      | Testes, segurança, performance, a11y, logs, erros                  | ✅     | 49 PHPUnit, 30 Vitest, 6 E2E, PHPStan 7, CI, SECURITY.md     |

## Segunda fase (fora do MVP, conforme o guia)

- Chat entre ONG e voluntário
- Avaliações pós-atividade
- Notificações avançadas (in-app/tempo real, preferências)
- Pagamentos / doações financeiras
- Gamificação (horas voluntariadas, conquistas)

## Melhorias técnicas sugeridas

- Geocodificação de endereço (CEP → coordenadas) para dispensar o clique no mapa.
- Verificação de e-mail e recuperação de senha.
- Painel administrativo para moderar ONGs (`OrganizationStatus`).
- Índice espacial nativo (`POINT` + `ST_Distance_Sphere`) se o volume crescer.
- Auditoria de acessos a dados pessoais.
