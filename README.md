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
php artisan claudavel:install
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
php artisan claudavel:install
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
| laravel/horizon     | Redis queue dashboard                           |
| laravel/reverb      | WebSockets without third-party services         |
| laravel/telescope   | Debug assistant                                 |
| laravel/pail        | Real-time log tailing                           |
| laravel/wayfinder   | Type-safe routes in TypeScript                  |
| sqids/sqids         | ID obfuscation                                  |

### GitHub Workflows

| Workflow                   | What it does                              |
| -------------------------- | ----------------------------------------- |
| claude-code-review.yml     | AI reviews every PR automatically         |
| claude.yml                 | Responds to @claude mentions              |
| ci.yml                     | Unified CI: Lint, Test (Postgres), Security |
| dependabot-automerge.yml   | Auto-merges minor/patch dependency updates |

**Note:** The unified `ci.yml` workflow runs three parallel jobs:
- **Lint** (~55s): PHP Pint, Wayfinder types, TypeScript, ESLint, Prettier
- **Test** (~1m20s): PostgreSQL 16, frontend build, Pest with coverage
- **Security** (~20s): Composer + npm audit

#### @claude Workflow Security

The `claude.yml` workflow gives Claude write access to your repository. This is intentional - it allows Claude to create branches, push commits, and open PRs on your behalf.

**Who can trigger Claude:**
- Only repository **owners** and **collaborators** can trigger Claude via @mentions
- External contributors and random users cannot trigger Claude, even on public repos

**What Claude can do:**
- Create and push branches
- Create pull requests (`gh pr create`)
- Merge pull requests (`gh pr merge`)
- Comment on issues and PRs
- Close issues

**What Claude cannot do:**
- Delete branches or force push
- Modify repository settings
- Access secrets beyond `CLAUDE_CODE_OAUTH_TOKEN`
- Trigger on comments from non-collaborators

If you need stricter controls, you can modify the workflow's `if` condition or remove `claude.yml` entirely with `--no-workflows`.

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
ADMIN_EMAILS=you@example.com,team@example.com
```

### Configuration

Claudavel publishes `config/claudavel.php` for package settings:

```php
// config/claudavel.php
return [
    'admin_emails' => env('ADMIN_EMAILS'),  // Comma-separated list
];
```

Admin emails control access to Horizon and Telescope in production. In local environment, everyone has access.

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
php artisan claudavel:install                      # Install everything (default)
php artisan claudavel:install --horizon            # Only Horizon (skip Reverb, Telescope)
php artisan claudavel:install --no-workflows       # Skip GitHub workflows
php artisan claudavel:install --force              # Overwrite existing files
```

The command is idempotent - run it on existing projects and it only installs what's missing.

## Development Server

`composer run dev` runs everything concurrently:

- Laravel dev server
- Horizon (queue worker)
- Reverb (WebSockets)
- Scheduler (`schedule:work`)
- Pail (log tailing)
- Vite (frontend)

## Repository Setup

Claudavel installs comprehensive GitHub repository setup scaffolding:

### Issue Templates
- **Bug Report** - Structured bug reports with area classification
- **Feature Request** - Feature requests with MVP priority tracking
- **Config** - Links to project board for roadmap visibility

### Pull Request Template
Laravel-specific compliance checklist:
- Code standards verification (`docs/standards/`)
- Type generation check (`php artisan types:generate`)
- Pint formatting (`vendor/bin/pint --dirty`)

### CODEOWNERS
Template file for automatic PR reviewer assignment.

### Husky + lint-staged Pre-commit Hooks
Automatically formats code before commits:
- PHP files → Laravel Pint
- JS/TS files → ESLint + Prettier
- CSS/JSON/MD/YAML → Prettier

**Setup:**
```bash
npm install --save-dev husky lint-staged
npx husky init
```

The installer creates `.husky/pre-commit` and adds `lint-staged` config to `package.json`.

### Branch Protection (Recommended)
Configure branch protection rules for `main`:
- Require status checks: Lint, Test, Security
- Dismiss stale reviews
- Don't enforce on admins (allows maintainer override)
- No force pushes or deletions

**Note:** Branch protection requires GitHub Pro for private repositories.

### ESLint Configuration
The ESLint config automatically ignores Wayfinder generated files:
- `resources/js/actions/**`
- `resources/js/routes/**`

This prevents import order and unused variable warnings for generated code.

## Requirements

- PHP 8.3+
- Laravel 12+
- Postgres
- Redis

## License

MIT
