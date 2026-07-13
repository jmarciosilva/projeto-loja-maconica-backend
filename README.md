# ARLS Gestão — Backend

API REST e painel administrativo em Laravel para a plataforma de gestão de Lojas Maçônicas. Consulte o [`README.md`](../README.md) da raiz para a visão geral do produto e o [`ROADMAP.md`](ROADMAP.md) desta pasta para o detalhamento técnico do backend.

## Stack

- PHP 8.3, Laravel 12.
- MySQL 8 como banco principal (persistência definida para todos os ambientes, incluindo desenvolvimento local via Laragon).
- Laravel Sanctum para autenticação por token (API) e sessão (painel web).
- Redis reservado para cache, filas e rate limit em estágios futuros (hoje `SESSION_DRIVER`, `CACHE_STORE` e `QUEUE_CONNECTION` usam o driver `database`).

## Arquitetura atual (Fase 1 — Fundação)

O código de domínio vive em `app/Modules/{ModuleName}`, conforme o padrão definido no [ROADMAP geral](../ROADMAP.md#14-boas-práticas-de-desenvolvimento). Apenas infraestrutura genuinamente transversal (`Controller` base, `AppServiceProvider`) permanece em `app/Http` e `app/Providers`.

- **`app/Modules/Administration`**: dados de Loja (tenant), usuários, papéis e permissões.
  - `Models`: `Lodge`, `User`, `Role`, `Permission`, `AuditLog` — todos com `uuid` público e soft delete quando aplicável.
  - `Http/Resources`: `LodgeResource`, `UserResource`.
  - RBAC básico via `Role::permissions()` e `User::hasPermission()`.
- **`app/Modules/Auth`**: fluxo de autenticação.
  - `Http/Controllers/AuthController.php`: login (token por dispositivo via Sanctum), `me` e `logout`, com registro em `audit_logs` a cada ação sensível.
  - `Http/Requests/LoginRequest.php`, `Http/Middleware/ResolveTenantFromHeader.php` (resolve o contexto da Loja pelo header `X-Lodge-Uuid`, valida que está ativa e impede acesso cruzado entre Lojas).

Cada módulo novo (Obreiros, Secretaria, Tesouraria...) deve seguir o mesmo padrão: `Models`, `Http/Controllers`, `Http/Requests`, `Http/Resources`, `Services`, `Repositories`, `Policies`, `Events`, `Listeners`, `Jobs` — apenas as pastas realmente necessárias.

**Nota sobre factories**: como os models não estão mais em `app/Models`, o `AppServiceProvider` registra um resolver de nomes de factory (`Factory::guessFactoryNamesUsing`) e cada factory declara `protected $model` explicitamente. Ao criar um novo model com factory, sempre declare `protected $model = SeuModel::class;` na respectiva factory.

## Requisitos

- PHP 8.3.
- Composer.
- MySQL 8 rodando (ex.: Laragon, porta padrão `3306`).
- Extensão PHP `zip` habilitada para instalar dependências Composer.

## Setup local

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

Ajuste no `.env` os dados de conexão MySQL (padrão do Laragon: usuário `root`, sem senha):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arls_gestao
DB_USERNAME=root
DB_PASSWORD=
```

Crie o banco (se ainda não existir) e rode as migrations com seed:

```bash
mysql -h127.0.0.1 -P3306 -uroot -e "CREATE DATABASE IF NOT EXISTS arls_gestao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8001
```

Usuário inicial criado pelo seed:

- E-mail: `admin@loja-piloto.test`
- Senha: `password`

## Endpoints atuais

- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`

Todas as rotas autenticadas passam por `auth:sanctum` e `ResolveTenantFromHeader`, exigindo o header `X-Lodge-Uuid` quando o usuário precisa trocar de contexto de Loja.

## Verificação

```bash
php artisan test
vendor/bin/pint --test
```

## Documentação relacionada

- [`ROADMAP.md`](ROADMAP.md): fases de evolução específicas do backend.
- [`../ROADMAP.md`](../ROADMAP.md): roadmap estratégico completo do produto (backend, mobile, landing page).
