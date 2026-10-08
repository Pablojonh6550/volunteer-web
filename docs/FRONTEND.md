# Frontend — Vue 3

SPA em **Vue 3 + TypeScript** (Composition API com `<script setup>`), **Pinia** para estado global, **Vue Router** e **Vite**. Consome exclusivamente a API Laravel.

## Estrutura

```
resources/js/
├── app.ts                 Bootstrap: Pinia, Router, tema, errorHandler
├── App.vue                Escolhe o layout pela rota; Toaster; anúncio de rota (a11y)
├── components/
│   ├── ui/                Design System (shadcn-vue/reka-ui): Button, Card, Input, Dialog,
│   │                      Sidebar, NativeSelect, Textarea...
│   ├── app/               Estrutura: AppSidebar, UserMenu, AppLogo, SkipLink, RouteAnnouncer
│   ├── common/            Reutilizáveis: FormField, PageHeader, StatusBadge, StatCard,
│   │                      EmptyState, LoadingState, ErrorState, Pagination, ConfirmDialog
│   ├── requests/          RequestCard, RequestFilters
│   ├── map/               MapView, LocationPicker
│   └── profile/           AddressFields, CategoryPicker
├── composables/           Lógica reutilizável (ver tabela abaixo)
├── layouts/               PublicLayout, AuthLayout, AppLayout
├── pages/                 public/ · auth/ · volunteer/ · organization/ · shared/
├── router/                routes.ts (lazy loading) · guards.ts (auth/perfil) · index.ts
├── services/              http.ts (axios + Sanctum + erros) e um service por recurso
├── stores/                auth (usuário/sessão) · categories (cache)
├── types/                 Contratos da API (models.ts, api.ts), enums e rótulos
├── utils/                 format.ts (datas, distância, máscaras) · geo.ts (Haversine)
└── tests/                 Vitest (unit/componentes)
```

## Fluxo de dados

```
Página ──► composable (useRequest, useSearch...) ──► service (requestService) ──► http (axios) ──► API
   ▲                 │
   └── estado reativo (data, loading, error) ◄──────────────── ApiError normalizado
```

- **services/** só fazem HTTP e tipam respostas.
- **composables/** encapsulam estado assíncrono, regras de UI e notificações.
- **stores/** guardam apenas estado realmente global (usuário logado, categorias).
- **pages/** compõem componentes e composables; não chamam axios diretamente.

## Composables

| Composable        | Responsabilidade                                                          |
| ----------------- | ------------------------------------------------------------------------- |
| `useAuth`         | Login, cadastro e logout com navegação (respeita `?redirect=` interno)    |
| `useAsync`        | Estado genérico `data/loading/error` + descarte de respostas antigas      |
| `useForm`         | Estado de formulário; mapeia erros 422 do Laravel para campos             |
| `useRequest`      | Uma solicitação: carregar, demonstrar/cancelar interesse, status, excluir |
| `useSearch`       | Pesquisa com filtros sincronizados à URL, debounce e paginação            |
| `useOrganization` | Carregar ONG / atualizar a própria                                        |
| `useVolunteer`    | Atualizar perfil do voluntário / "Minhas atividades"                      |
| `useLocation`     | Geolocalização do navegador (sob demanda, estado compartilhado)           |
| `useMap`          | Leaflet carregado sob demanda; marcadores, raio, popups seguros           |
| `useNotification` | Toasts de sucesso/erro/info                                               |
| `useAppearance`   | Tema claro/escuro/sistema                                                 |

## Stores (Pinia)

- **`auth`** — `user`, `isAuthenticated`, `isOrganization`, `isVolunteer`, `homeRouteName`, `fetchUser()` (deduplicado), `login`, `register`, `logout`. Registra o handler de 401 do cliente HTTP (sessão expirada limpa o estado).
- **`categories`** — carrega uma vez e agrupa por tipo (`byType`, `forType()`).

Nenhum dado sensível é persistido em `localStorage` (apenas a preferência de tema).

## Rotas

| Rota                                     | Página                   | Perfil     |
| ---------------------------------------- | ------------------------ | ---------- |
| `/` · `/como-funciona`                   | Home · Como funciona     | público    |
| `/entrar` · `/cadastro`                  | Login · Cadastro         | visitante  |
| `/voluntario`                            | Dashboard                | voluntário |
| `/voluntario/perfil`                     | Perfil                   | voluntário |
| `/voluntario/mapa`                       | Mapa de ONGs             | voluntário |
| `/voluntario/ongs/:id`                   | Detalhes da ONG          | voluntário |
| `/voluntario/solicitacoes`               | Pesquisa de solicitações | voluntário |
| `/voluntario/solicitacoes/:id`           | Detalhes + interesse     | voluntário |
| `/voluntario/atividades`                 | Minhas atividades        | voluntário |
| `/voluntario/configuracoes`              | Configurações            | voluntário |
| `/ong`                                   | Dashboard                | ONG        |
| `/ong/perfil`                            | Perfil da ONG            | ONG        |
| `/ong/solicitacoes`                      | Lista de solicitações    | ONG        |
| `/ong/solicitacoes/nova` · `/:id/editar` | Criar / editar           | ONG        |
| `/ong/solicitacoes/:id`                  | Detalhes                 | ONG        |
| `/ong/solicitacoes/:id/voluntarios`      | Voluntários interessados | ONG        |
| `/ong/configuracoes`                     | Configurações            | ONG        |

`meta` de cada rota: `layout`, `requiresAuth`, `guestOnly`, `role`, `title`. O guard (`router/guards.ts`) redireciona visitantes ao login e usuários para o painel do próprio perfil. A autorização definitiva é sempre da API.

## Integração com a API

`services/http.ts`:

- `withCredentials` + `withXSRFToken`: cookies de sessão e CSRF do Sanctum.
- `ensureCsrfCookie()` antes de login/cadastro.
- **419** → renova o CSRF e repete a requisição uma vez.
- **401** → limpa o usuário da store.
- Todos os erros viram `ApiError { status, message, errors }`; mensagens de erros 5xx são substituídas por texto genérico (não vazam detalhes internos).

## Mapas e geolocalização

- **Leaflet + OpenStreetMap**, importado dinamicamente em `useMap` (chunk `leaflet` separado, só carregado em páginas com mapa).
- Marcadores `divIcon` estilizados em `app.css`, focáveis por teclado; popups montados com `textContent` (sem `innerHTML` → sem XSS).
- `LocationPicker`: "usar minha localização" ou clique no mapa.
- A distância oficial vem da API (`distance_km`); `utils/geo.ts` oferece Haversine no cliente para usos locais.
- A geolocalização **nunca é solicitada automaticamente** — só por clique do usuário.

## Performance

- Lazy loading de todas as páginas (um chunk por tela).
- `manualChunks`: `vue` (vue, router, pinia) e `leaflet` separados → cache de longo prazo.
- Nginx serve `/build/*` com `Cache-Control: immutable` e gzip.
- Categorias em cache (store + cache Redis na API); `fetchUser` deduplicado.
- Busca com debounce (350 ms) e descarte de respostas fora de ordem.

## Acessibilidade

- Link **"Pular para o conteúdo"** e `<main tabindex="-1">`.
- **RouteAnnouncer**: anuncia a página carregada (`aria-live`) e move o foco ao conteúdo a cada navegação.
- `FormField` associa `label`, dica e erro (`aria-describedby`, `aria-invalid`, `role="alert"`).
- Selects nativos, rótulos visíveis, `aria-current` na navegação, `aria-pressed` em filtros, `aria-busy` em listas.
- Status nunca comunicado só por cor (badges sempre com texto).
- Mapa é complemento: toda informação também aparece em lista.
- Diálogos (reka-ui) com foco preso e fechamento por Esc.
- `prefers-reduced-motion` respeitado; tema escuro.

## Mapa das fases do PDI

| Fase | Conhecimento    | Onde está aplicado                                                                                                                         |
| ---- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| 1    | Composition API | Todas as páginas/componentes com `<script setup>`, `ref/computed/watch`                                                                    |
| 2    | Componentização | `components/ui` (Design System), `common/`, `requests/`, `map/`, `profile/`                                                                |
| 3    | Composables     | `useAuth`, `useRequest`, `useOrganization`, `useVolunteer`, `useLocation`, `useMap`, `useSearch`, `useNotification`, `useForm`, `useAsync` |
| 4    | Pinia           | `stores/auth.ts`, `stores/categories.ts`                                                                                                   |
| 5    | TypeScript      | `types/` (contratos da API, enums), props/emit tipados, `vue-tsc` no CI                                                                    |
| 6    | Forms           | `useForm` + `FormField`; cadastro, perfis, solicitação, configurações                                                                      |
| 7    | Integração API  | `services/`, `http.ts` (CSRF, 401/419, erros), estados de loading/erro/vazio                                                               |
| 8    | Mapas           | `useMap`, `MapView`, `LocationPicker`, `OrganizationsMapPage`                                                                              |
| 9    | Performance     | Lazy loading, chunks, cache, debounce                                                                                                      |
| 10   | Testes          | Vitest (`resources/js/tests`) + Playwright (`e2e/`)                                                                                        |
| 11   | Acessibilidade  | Seção acima; testes de a11y em Vitest e E2E                                                                                                |
