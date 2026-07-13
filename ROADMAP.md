# ROADMAP — Backend (ARLS Gestão)

Recorte técnico do [ROADMAP geral](../ROADMAP.md) focado no que precisa existir na API/painel Laravel em cada fase. Este arquivo acompanha o progresso; o roadmap da raiz é a fonte estratégica (visão de produto, módulos, segurança, escalabilidade).

## Status atual: Fase 1 — Fundação do Projeto (em andamento)

Concluído:

- [x] Estrutura inicial Laravel 12.
- [x] Persistência em MySQL 8 configurada (local via Laragon: host `127.0.0.1`, porta `3306`, banco `arls_gestao`).
- [x] Autenticação com Sanctum (login, `me`, logout, token por dispositivo).
- [x] Modelo inicial de `Lodge` (tenant), `User`, `Role`, `Permission` e vínculos `role_user` / `permission_role`.
- [x] Middleware `ResolveTenantFromHeader` para resolução do tenant via `X-Lodge-Uuid` e bloqueio de acesso cruzado entre Lojas.
- [x] `audit_logs` com registro de login/logout.
- [x] Testes de feature cobrindo login/me e isolamento entre Lojas.
- [x] Seed de Loja piloto, permissões básicas, papel administrador e usuário inicial.
- [x] Estrutura modular `app/Modules/{ModuleName}/...` aplicada (`Administration`: models/resources de Loja, usuário, papel, permissão e auditoria; `Auth`: controller, request e middleware do fluxo de autenticação).

Pendente antes de fechar a Fase 1:

- [ ] Global scope automático por `lodge_id` nos models tenant-scoped (hoje o isolamento depende do middleware, não de scope automático nas queries Eloquent).
- [ ] Policies e Gates por recurso (hoje só existe `User::hasPermission()`).
- [ ] CI com testes, Pint e análise estática (Larastan/PHPStan).
- [ ] Layout inicial do painel administrativo web (ainda não iniciado).

## Próximas fases (recorte backend)

Ver descrição completa de cada fase, critérios de aceite, dependências e riscos no [ROADMAP geral](../ROADMAP.md#4-roadmap-por-fases).

- **Fase 2 — Gestão de Obreiros**: models e migrations de `members`, contatos, família, grau, cargos e histórico; endpoints REST; regras de visibilidade de dados sensíveis.
- **Fase 3 — Secretaria**: `secretary_records`, `correspondences`, `documents`, `document_versions`, versionamento e auditoria.
- **Fase 4 — Tesouraria**: `financial_categories`, `financial_transactions`, `monthly_fees`, `payments`, fluxo de caixa e relatórios.
- **Fase 5 — Sessões**: calendário, presença, check-in (QR Code), convites.
- **Fase 6 — Eventos**: eventos internos/públicos, participantes, integração com endpoints públicos da landing.
- **Fase 7 em diante**: comunicação, biblioteca, hospitalaria, landing page, dashboard executivo, evolução SaaS — ver roadmap geral.

## Convenções técnicas do backend

- Código de domínio em `app/Modules/{ModuleName}/...` (ver [`README.md`](README.md)); `app/Http` e `app/Providers` reservados para infraestrutura transversal.
- `id` interno + `uuid` público em toda entidade exposta pela API.
- `lodge_id` obrigatório em entidades tenant-scoped.
- `created_at`, `updated_at`, `deleted_at` (soft delete) quando aplicável.
- API versionada sob `/api/v1`.
- Form Requests para validação, API Resources para resposta, Services para regra de negócio quando envolver múltiplos models ou transações.
- Permissões no formato `modulo.acao` (ex.: `members.create`).
