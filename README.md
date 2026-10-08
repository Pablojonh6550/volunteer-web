# Conecta ONGs — Plataforma de ONGs e Voluntários

Plataforma que conecta **organizações não governamentais** a **voluntários**: ONGs publicam solicitações de **doações, serviços e voluntariado**; voluntários encontram oportunidades **próximas**, demonstram interesse e acompanham seu histórico.

Projeto desenvolvido como PDI de aprofundamento em **Vue 3 + Laravel API + PHP 8.4**, seguindo o _Guia de Desenvolvimento — Plataforma de ONGs e Voluntários_.

## Stack

| Camada         | Tecnologia                                                             |
| -------------- | ---------------------------------------------------------------------- |
| Backend / API  | Laravel 13 + PHP 8.4                                                   |
| Frontend       | Vue 3 (Composition API) + TypeScript                                   |
| Estado         | Pinia                                                                  |
| Rotas          | Vue Router (lazy loading por página)                                   |
| Build          | Vite (via Vite+)                                                       |
| Banco          | MySQL 8.4                                                              |
| Cache / Filas  | Redis + Laravel Queue                                                  |
| Infraestrutura | Docker / Docker Compose (Nginx → PHP-FPM → MySQL/Redis)                |
| Autenticação   | Laravel Sanctum (SPA com cookie + token Bearer para clientes externos) |
| Mapa           | Leaflet + OpenStreetMap + Geolocation API                              |
| Testes         | PHPUnit · Vitest + Vue Test Utils · Playwright (E2E)                   |
| Qualidade      | PHPStan (nível 7), Pint, Oxlint/Oxfmt (Vite+), vue-tsc                 |
| CI             | GitHub Actions                                                         |

## Início rápido

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
npm install && npm run build
```

Acesse **http://localhost:8000**. Contas de demonstração (senha `password`):

| Perfil     | E-mail                   |
| ---------- | ------------------------ |
| ONG        | `ong@example.com`        |
| Voluntário | `voluntario@example.com` |

Guia completo (HMR, portas, troubleshooting): [docs/SETUP.md](docs/SETUP.md).

## Funcionalidades (MVP)

**ONG** — cadastro e dados institucionais · endereço, contatos e localização no mapa · criar, editar, iniciar, concluir e cancelar/excluir solicitações · ver interessados · aprovar/recusar/concluir participações · dashboard com indicadores.

**Voluntário** — perfil pessoal e áreas de interesse · localização com consentimento (LGPD) · mapa de ONGs próximas · pesquisa por texto, tipo, categoria, prioridade, status e distância · detalhes e manifestação de interesse · "Minhas atividades" com histórico · dashboard.

**Fluxo de interesse:** voluntário demonstra interesse → ONG analisa → aprova/recusa → atividade é realizada → participação concluída.

## Documentação

| Documento                                    | Conteúdo                                                              |
| -------------------------------------------- | --------------------------------------------------------------------- |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Arquitetura, camadas, modelagem do banco, enums e regras de negócio   |
| [docs/API.md](docs/API.md)                   | Referência completa dos endpoints (payloads, respostas, erros)        |
| [docs/FRONTEND.md](docs/FRONTEND.md)         | Arquitetura Vue 3, composables, stores, rotas e mapa das fases do PDI |
| [docs/SECURITY.md](docs/SECURITY.md)         | Segurança e LGPD                                                      |
| [docs/TESTING.md](docs/TESTING.md)           | Estratégia e execução dos testes                                      |
| [docs/SETUP.md](docs/SETUP.md)               | Ambiente de desenvolvimento, Docker, DevOps e produção                |
| [docs/ROADMAP.md](docs/ROADMAP.md)           | Sprints do guia, status e próxima fase                                |

## Comandos úteis

```bash
# Backend (dentro do container)
docker compose exec app php artisan test            # testes PHPUnit
docker compose exec app vendor/bin/phpstan analyse  # análise estática
docker compose exec app vendor/bin/pint             # formatação PHP

# Frontend (no host)
npm run dev          # Vite com HMR
npm run check        # lint + formatação
npm run types:check  # vue-tsc
npm test             # Vitest
npm run test:e2e     # Playwright (requer a stack rodando)
```
