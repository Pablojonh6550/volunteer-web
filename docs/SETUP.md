# Ambiente, Docker e DevOps

## Serviços (docker-compose.yml)

```
Nginx (:8000) → PHP-FPM 8.4 (app) → MySQL 8.4
                   ├→ Redis 7 (cache e filas)
                   ├→ queue      php artisan queue:work
                   ├→ scheduler  php artisan schedule:work
                   └→ vite       (opcional, profile "dev") HMR na porta 5173
```

## Primeira execução

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
npm install
npm run build          # ou npm run dev para HMR
```

App em http://localhost:8000 — contas `ong@example.com` / `voluntario@example.com`, senha `password`.

### Desenvolvimento com HMR

No host: `npm run dev` (Vite em :5173; o Laravel injeta os assets via `@vite`). Ou no Docker: `docker compose --profile dev up -d vite` (usa `node_modules` em volume próprio para não sobrescrever o do host).

> Se `public/hot` existir, o Laravel carrega os assets do servidor Vite. Remova-o se o Vite não estiver rodando.

### Portas

| Variável          | Padrão | Uso                                            |
| ----------------- | ------ | ---------------------------------------------- |
| `APP_PORT`        | 8000   | Nginx                                          |
| `FORWARD_DB_PORT` | 3306   | MySQL no host (troque se 3306 estiver ocupada) |
| `VITE_PORT`       | 5173   | Vite                                           |

### Variáveis importantes

| Variável                   | Descrição                                          |
| -------------------------- | -------------------------------------------------- |
| `SANCTUM_STATEFUL_DOMAINS` | Domínios do SPA que usam cookie de sessão          |
| `SESSION_DOMAIN`           | Domínio do cookie de sessão (produção)             |
| `QUEUE_CONNECTION=redis`   | Filas no Redis (worker `queue`)                    |
| `CACHE_STORE=redis`        | Cache no Redis                                     |
| `REDIS_CLIENT=predis`      | Cliente Redis em PHP puro (sem extensão)           |
| `APP_LOCALE=pt_BR`         | Mensagens de validação em português (`lang/pt_BR`) |
| `MAIL_MAILER=log`          | E-mails vão para `storage/logs` em desenvolvimento |

## Troubleshooting

- **502 Bad Gateway** após recriar o container `app`: `docker compose restart nginx`.
- **419 / CSRF**: confira `SANCTUM_STATEFUL_DOMAINS` com host **e** porta usados no navegador.
- **Git "dubious ownership" no container**: `docker compose exec app git config --global --add safe.directory /var/www`.

## Produção

- Build de assets no CI (`npm ci && npm run build`) e `composer install --no-dev --optimize-autoloader`.
- `php artisan config:cache route:cache view:cache event:cache`.
- Migrations: `php artisan migrate --force` **em etapa separada do deploy**, com backup antes; evitar migrations destrutivas (`DB::prohibitDestructiveCommands` ativo em produção).
- Worker de fila supervisionado (systemd/supervisor ou container `queue`), com `queue:restart` a cada deploy.
- Scheduler: `schedule:work` (container) ou cron `* * * * * php artisan schedule:run`.
- Backups: `mysqldump` diário + retenção; volume `redis-data` com AOF.
- Logs: canal `stack`/`daily`; health check em `/up`.
- Configuração exclusivamente por variáveis de ambiente.

## CI (GitHub Actions — `.github/workflows/tests.yml`)

| Job      | Etapas                                                           |
| -------- | ---------------------------------------------------------------- |
| backend  | `composer install` → `composer audit` → Pint → PHPStan → PHPUnit |
| frontend | `npm ci` → lint/format → vue-tsc → Vitest → build                |
| e2e      | sobe a stack Docker → migra/semeia → build → Playwright          |
