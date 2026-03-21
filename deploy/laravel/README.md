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

## JWT / Sanctum

Se usar `JWT_SECRET` por env, alinhar leitura a partir de `JWT_SECRET_FILE` de forma análoga, ou manter variável injetada pelo pipeline.
