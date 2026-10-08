# Segurança e LGPD

| Item do guia                       | Implementação                                                                                                                                                                                                       |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sanctum e autenticação segura      | SPA com cookie de sessão **HttpOnly** + CSRF (`statefulApi()`); sessão regenerada no login (anti-fixação) e invalidada no logout; token Bearer apenas para clientes externos                                        |
| Policies e autorização por recurso | `app/Policies/*` + middleware `user.type` (ver ARCHITECTURE.md)                                                                                                                                                     |
| Form Requests e validação          | `app/Http/Requests/*` — tipos, tamanhos, enums, `exists`, datas coerentes, categoria compatível com o tipo                                                                                                          |
| Rate limiting                      | Limitadores `auth`, `api`, `uploads` + bloqueio de login por e-mail/IP (`LoginRequest`)                                                                                                                             |
| XSS                                | Vue escapa toda interpolação; **não usamos `v-html`**; popups do mapa via `textContent`; headers `X-Content-Type-Options`, `X-Frame-Options`                                                                        |
| SQL Injection                      | Eloquent/Query Builder com bindings; expressões raw (Haversine, ordenação) só interpolam nomes de tabela e valores de enum, nunca entrada do usuário                                                                |
| Upload seguro                      | Avatar: MIME real (`mimes` + `mimetypes`), 2 MB, dimensões máximas, SVG bloqueado, nome aleatório, disco `public`; Nginx nega execução de PHP em `/storage`                                                         |
| Hash de senhas                     | Cast `hashed` (bcrypt); política forte em produção (12+ caracteres, maiúsculas, números, símbolos, não vazada)                                                                                                      |
| Exposição de dados pessoais        | `VolunteerResource`: terceiros veem só perfil público; contato liberado apenas após aprovação (`RequestVolunteerResource`); e-mails de notificação sem dados do voluntário; `UserResource` nunca expõe senha/tokens |
| Localização e consentimento        | `location_consent_at`; coordenadas descartadas sem consentimento e apagadas ao revogar; geolocalização do navegador só por ação do usuário; endereço do voluntário nunca exposto                                    |
| Logs sem informações sensíveis     | `dontFlash` (senhas, documento); contexto de log apenas `user_id`; mensagens de erro 5xx não chegam ao usuário                                                                                                      |

## LGPD — direitos do titular

- **Consentimento**: aceite obrigatório dos termos no cadastro (`terms`) e consentimento específico e revogável para localização.
- **Minimização**: não existe listagem de voluntários; ONGs só veem quem demonstrou interesse nas suas solicitações.
- **Acesso e correção**: o próprio usuário visualiza e edita todos os seus dados (perfil e configurações).
- **Eliminação**: `DELETE /api/me` remove conta, perfil, participações (cascata) e avatar.

## Checklist de produção

- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS com `SESSION_SECURE_COOKIE=true`.
- `SANCTUM_STATEFUL_DOMAINS` e `SESSION_DOMAIN` com o domínio real.
- `DB::prohibitDestructiveCommands()` já ativo em produção.
- Rodar `composer audit` e `npm audit` no CI.
