<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Sousa Lima Consultoria — app `admin`

Este diretório é o **backend Laravel** do ecossistema SLC (API, painel futuro, integração com o site Astro). Documentação de infra e deploy: [deploy/guia-deploy-e-atualizacao.md](../deploy/guia-deploy-e-atualizacao.md), [deploy/laravel/README.md](../deploy/laravel/README.md), [docs/README.md](../docs/README.md).

### Desenvolvimento local

- Copie `.env` a partir de `.env.example` se necessário; **não** versionar `.env`.
- Por defeito o projeto usa **SQLite** em `database/database.sqlite` (ignorado no Git).
- `php artisan migrate` para aplicar migrações.

### Credenciais e produção

Variáveis sensíveis (`APP_KEY`, base de dados, mail, etc.) são definidas no ambiente ou em **secrets** Swarm em produção; gerar `APP_KEY` com `php artisan key:generate` quando for preparar o deploy (ver guia acima).

### Painel visual (Filament)

- Painel administrativo em **`/admin`**.
- Stack do painel: **Filament** (sobre Laravel, com guard `web`).
- Recursos atuais no painel: **Services**, **Case Studies** e **Leads**.

#### Setup rápido

```bash
composer install
php artisan filament:install
php artisan migrate
php artisan db:seed
php artisan serve
```

#### Controle de acesso

- Definir `FILAMENT_ADMIN_EMAILS` no ambiente (lista separada por vírgula).
- Apenas utilizadores com e-mail nesta whitelist podem aceder ao painel.
- Em `local`, se `FILAMENT_ADMIN_EMAILS` estiver vazio, o acesso é liberado para acelerar desenvolvimento.

#### Próximos passos operacionais

1. Fechar workflow de publicação no painel (status `draft/published` + webhook para rebuild do frontend).
2. Refinar validações/sanitização de `content_html` no fluxo de criação/edição.
3. Adicionar política de permissões por perfil (ex.: editor vs admin).
4. Incluir testes de regressão do painel no CI do repositório admin.
