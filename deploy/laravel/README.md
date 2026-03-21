# Ajustes no repositório Laravel (admin)

Este diretório não contém a app completa — apenas **referências** para copiar no projeto **Laravel** que alimenta a imagem `eolimabr/php8.4-sousalima-multitenant`.

Os ficheiros montados no contentor usam o **mesmo nome** que o secret Swarm (evita ambiguidade com outras apps no servidor):

| Variável de ambiente (definida no `deploy/slc.yaml`) | Ficheiro no contentor |
|------------------------------------------------------|------------------------|
| `DB_PASSWORD_FILE` | `/run/secrets/slc_sousalima_db_password` |
| `APP_KEY_FILE` | `/run/secrets/slc_sousalima_app_key` |
| `JWT_SECRET_FILE` | `/run/secrets/slc_sousalima_jwt_secret` |
| `MAIL_PASSWORD_FILE` | `/run/secrets/slc_sousalima_smtp_password` |

**Gerar e registar secrets:** ver [guia-deploy-e-atualizacao.md](../guia-deploy-e-atualizacao.md) — `php artisan key:generate --show` para `slc_sousalima_app_key`; `openssl rand -base64 48 | tr -d '\n' | docker secret create slc_sousalima_jwt_secret -` para JWT.

## `config/database.php` — password PostgreSQL

Na conexão `pgsql`, substituir a chave `password` por:

```php
'password' => env('DB_PASSWORD') ?: (
    env('DB_PASSWORD_FILE') && is_readable(env('DB_PASSWORD_FILE'))
        ? trim(file_get_contents(env('DB_PASSWORD_FILE')))
        : ''
),
```

## `config/app.php` — APP_KEY por ficheiro (opcional)

Se não injetar `APP_KEY` em texto no `.env`, ler de `APP_KEY_FILE`:

```php
'key' => env('APP_KEY') ?: (
    env('APP_KEY_FILE') && is_readable(env('APP_KEY_FILE'))
        ? trim(file_get_contents(env('APP_KEY_FILE')))
        : ''
),
```

(Ajustar se o projeto já usa outro mecanismo.)

## `config/mail.php` — Microsoft 365 (SMTP)

O stack define `MAIL_HOST=smtp.office365.com`, `MAIL_PORT=587`, `MAIL_ENCRYPTION=tls` e `MAIL_PASSWORD_FILE`. Na password do mailer:

```php
'password' => env('MAIL_PASSWORD') ?: (
    env('MAIL_PASSWORD_FILE') && is_readable(env('MAIL_PASSWORD_FILE'))
        ? trim(file_get_contents(env('MAIL_PASSWORD_FILE')))
        : ''
),
```

Definir **`MAIL_USERNAME`** no `slc.yaml` com o **endereço completo** da caixa licenciada no Microsoft 365 (ex.: `noreply@sousalimaconsultoria.com.br`). A password fica no secret `slc_sousalima_smtp_password` (conta com SMTP autenticado; com MFA, a Microsoft pode exigir **password de aplicação** — ver documentação atual do tenant).

## JWT (`tymon/jwt-auth`)

`config/jwt.php` usa `JWT_SECRET` ou, em alternativa, **`JWT_SECRET_FILE`** (no Swarm: secret `slc_sousalima_jwt_secret` já montado no `slc.yaml`).

| Método | Rota | Descrição |
|--------|------|-----------|
| `POST` | `/api/v1/auth/login` | `email`, `password` → `access_token`, `token_type`, `expires_in` |
| `POST` | `/api/v1/auth/refresh` | Renovar JWT (Bearer com token dentro da janela de refresh) |
| `POST` | `/api/v1/auth/logout` | Invalidar JWT atual (`jwt.auth`) |
| `GET` | `/api/v1/auth/me` | Dados do utilizador autenticado (`jwt.auth`) |
| `GET` | `/api/v1/leads` | Lista paginada de leads (`?page`, `?per_page` ≤ 100); só JWT |

Na **Swagger UI** (`/api/documentation`), há **jwtAuth** (login) e **bearerAuth** (`API_READ_TOKEN`). **Auth/me**, **logout** e **GET /leads** exigem só JWT. Conteúdo e POST de lead aceitam **JWT ou** `API_READ_TOKEN` (middleware `api.public`).

Secret local: `php artisan jwt:secret` (não commitar o `.env`).

## API v1 (`/api/v1/*`) — site / build (híbrido)

Implementação em `admin/routes/api.php`. GET de conteúdo e POST de lead: **Authorization: Bearer** com **JWT** *ou* **`API_READ_TOKEN`** (definir no `.env` / CI). Em `local`, com `API_READ_TOKEN` vazio, o middleware permite pedidos sem Bearer (apenas desenvolvimento).

| Método | Rota | Auth |
|--------|------|------|
| `GET` | `/api/v1/content/slugs` | JWT ou `API_READ_TOKEN` |
| `GET` | `/api/v1/cases` (opcional `?status=published`) | idem |
| `GET` | `/api/v1/cases/{slug}` | idem |
| `GET` | `/api/v1/services` | idem |
| `GET` | `/api/v1/services/{slug}` | idem |
| `POST` | `/api/v1/lead/contact` | idem (CORS + throttle; ver [formulario-contato-lead-slc.md](../../docs/conhecimento/formulario-contato-lead-slc.md)) |

- **`API_READ_TOKEN`:** token estático partilhado com o pipeline de build (não commitar). Alternativa ao JWT para máquinas sem login.
- **`CORS_ALLOWED_ORIGINS`:** origens autorizadas (separadas por vírgula) para pedidos com credenciais/CORS ao POST de lead.
- **Migrações / seed:** `php artisan migrate --force` e opcionalmente `php artisan db:seed --class=Database\\Seeders\\ContentApiSeeder` no contentor.

## OpenAPI (L5 Swagger)

Pacote **`darkaonline/l5-swagger`**: documentação interativa em **`/api/documentation`** (UI Swagger) e JSON gerado em `storage/api-docs/api-docs.json`.

- **Gerar/atualizar spec** após mudar atributos OpenAPI nos controladores: `php artisan l5-swagger:generate`
- **Produção:** com `L5_SWAGGER_GENERATE_ALWAYS=false` (predefinição), o ficheiro JSON só atualiza quando corres o comando acima (ex. no deploy). Em desenvolvimento podes usar `L5_SWAGGER_GENERATE_ALWAYS=true`.
- **Host na UI:** opcional `L5_SWAGGER_CONST_HOST=https://api.sousalimaconsultoria.com.br` no `.env`.
- Metadados globais em `app/OpenApi/OpenApiSpec.php`; operações documentadas com `OpenApi\Attributes` nos controladores `Api\V1\*`.
