# Claudavel

[![CI](https://github.com/StuMason/claudavel/actions/workflows/ci.yml/badge.svg)](https://github.com/StuMason/claudavel/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/stumason/claudavel.svg)](https://packagist.org/packages/stumason/claudavel)
[![PHP Version](https://img.shields.io/packagist/php-v/stumason/claudavel.svg)](https://packagist.org/packages/stumason/claudavel)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/stumason/claudavel.svg)](LICENSE)

**Laravel + Claude Code, configured to work together.**

One command installs a complete Laravel stack with AI-powered code review, consistent coding standards that Claude actually follows, and GitHub workflows that let you @mention Claude in issues and PRs.

```bash
composer require stumason/claudavel
php artisan claudavel:install --all
```

## Why This Exists

Claude Code is powerful, but it works better when your project has clear conventions. Claudavel gives you:

- **Coding standards Claude follows** - Published to `docs/standards/` and referenced in CLAUDE.md. No more explaining the same patterns every session.
- **AI code review on every PR** - Claude reviews your code automatically via GitHub Actions. Catches bugs, suggests improvements, checks against your standards.
- **@claude in issues and PRs** - Mention Claude anywhere in GitHub and it responds with context about your codebase.
- **A complete stack** - Fortify, Sanctum, Horizon, Reverb, Telescope, Pail, Wayfinder, Sqids. All configured, all working together.

## Quick Start

```bash
laravel new my-app
cd my-app
composer require stumason/claudavel
php artisan claudavel:install --all
createdb my_app
composer run dev
```

Add `CLAUDE_CODE_OAUTH_TOKEN` to your GitHub repo secrets to enable AI features.

## What Gets Installed

### Packages

| Package             | Purpose                                         |
| ------------------- | ----------------------------------------------- |
| laravel/fortify     | Authentication without Breeze/Jetstream bloat   |
| laravel/sanctum     | API tokens                                      |
| laravel/horizon     | Redis queue dashboard (optional)                |
| laravel/reverb      | WebSockets without third-party services (optional) |
| laravel/telescope   | Debug assistant (optional)                      |
| laravel/pail        | Real-time log tailing                           |
| laravel/wayfinder   | Type-safe routes in TypeScript                  |
| sqids/sqids         | ID obfuscation                                  |

### GitHub Workflows

| Workflow                   | What it does                              |
| -------------------------- | ----------------------------------------- |
| claude-code-review.yml     | AI reviews every PR automatically         |
| claude.yml                 | Responds to @claude mentions              |
| tests.yml                  | Runs Pest with Postgres                   |
| lint.yml                   | Pint + ESLint + Prettier                  |
| dependabot-automerge.yml   | Auto-merges minor/patch dependency updates |

### Coding Standards

Published to `docs/standards/` with conventions that matter:

- **Actions pattern** - Business logic in `app/Actions/{Domain}/`
- **DTOs** - Type-safe data containers in `app/DataTransferObjects/`
- **Money as integers** - Store cents, not dollars
- **Lowercase imports** - `@/components/button` not `@/Components/Button`

These get referenced in your CLAUDE.md so every Claude session knows your conventions.

### Environment

```env
DB_CONNECTION=pgsql
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

## Features

### ID Obfuscation

Never expose sequential IDs. Add the trait to any model:

```php
use App\Models\Traits\HasUid;

class User extends Model
{
    use HasUid;
}

$user->uid;  // "K4x9Pq" instead of "1"
User::findByUid('K4x9Pq');  // Works
Route::get('/users/{user}', ...);  // Binds automatically
```

### Generator Commands

```bash
php artisan make:action User/UpdateProfile
php artisan make:dto UserProfile --properties=id:int,name:string,email:?string
php artisan types:generate  # Generates TypeScript interfaces from DTOs
```

### Health Check

The `/health` endpoint verifies database, Redis, cache, queue, and storage. Use it for load balancer checks.

## Installation Options

```bash
php artisan claudavel:install                      # Interactive prompts
php artisan claudavel:install --all                # Install everything
php artisan claudavel:install --horizon --reverb   # Pick specific packages
php artisan claudavel:install --no-workflows       # Skip GitHub workflows
php artisan claudavel:install --force              # Overwrite existing files
```

The command is idempotent - run it on existing projects and it only installs what's missing.

## Requirements

- PHP 8.3+
- Laravel 12+
- Postgres
- Redis

## License

MIT
