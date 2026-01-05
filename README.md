# Claudavel

[![CI](https://github.com/StuMason/claudavel/actions/workflows/ci.yml/badge.svg)](https://github.com/StuMason/claudavel/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/stumason/claudavel.svg)](https://packagist.org/packages/stumason/claudavel)
[![PHP Version](https://img.shields.io/packagist/php-v/stumason/claudavel.svg)](https://packagist.org/packages/stumason/claudavel)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/stumason/claudavel.svg)](LICENSE)

**Zero to production-ready Laravel in one command.**

Claudavel is an opinionated Laravel starter that configures everything you need for a modern, AI-assisted development workflow. Stop wasting the first hour of every project on boilerplate.

## Why Claudavel?

- **One command** installs Fortify, Sanctum, Horizon, Reverb, Telescope, Pail, and Wayfinder
- **Postgres + Redis** configured out of the box (because SQLite doesn't scale)
- **ID obfuscation** via Sqids - never expose sequential IDs to users
- **Actions pattern** scaffolded - keep controllers thin, business logic testable
- **AI-ready** - coding standards that Claude/GPT actually follow
- **`composer run dev`** starts everything: server, queue, websockets, logs, vite

## Quick Start

```bash
composer require stumason/claudavel --dev
php artisan claudavel:install --all
createdb your_project_name
composer run dev
```

That's it. Visit `/health` to verify everything's working.

## What Gets Installed

### Core (always)

| Package           | Purpose                    |
| ----------------- | -------------------------- |
| laravel/fortify   | Authentication backend     |
| laravel/sanctum   | API tokens                 |
| laravel/pail      | Real-time log tailing      |
| laravel/wayfinder | Type-safe route generation |
| sqids/sqids       | ID obfuscation             |

### Optional (prompted)

| Package           | Purpose               |
| ----------------- | --------------------- |
| laravel/horizon   | Redis queue dashboard |
| laravel/reverb    | WebSocket server      |
| laravel/telescope | Debug assistant       |

## Installation Options

```bash
php artisan claudavel:install                      # Interactive prompts
php artisan claudavel:install --all                # Install everything
php artisan claudavel:install --horizon --reverb   # Pick specific packages
php artisan claudavel:install --no-interaction     # CI/CD friendly
php artisan claudavel:install --force              # Overwrite existing files
php artisan claudavel:install --no-workflows       # Skip GitHub workflows
php artisan claudavel:install my-app               # Custom database name
```

## What Changes

### Files Created

```
app/
├── Actions/                    # Business logic goes here
├── DataTransferObjects/        # Type-safe data containers
├── Models/Traits/HasUid.php    # ID obfuscation trait
├── Services/SqidService.php    # Sqid encoding/decoding
├── Http/Controllers/HealthCheckController.php
└── Providers/
    ├── HorizonServiceProvider.php   (if installed)
    └── TelescopeServiceProvider.php (if installed)

config/sqids.php
resources/js/pages/health-check.tsx
docs/standards/                 # AI-friendly coding standards

.prettierrc
.prettierignore
.editorconfig
eslint.config.js

.github/
├── workflows/
│   ├── tests.yml               # Pest tests with Postgres
│   ├── lint.yml                # Pint + ESLint + Prettier
│   ├── claude-code-review.yml  # AI code review on PRs
│   ├── claude.yml              # @claude mentions
│   └── dependabot-automerge.yml
└── dependabot.yml              # Automated dependency updates
```

### Environment

```env
DB_CONNECTION=pgsql
DB_DATABASE=your_project_name
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

### Composer Scripts

```bash
composer run dev   # Runs server, horizon, reverb, pail, vite concurrently
```

## ID Obfuscation

Every model can expose obfuscated IDs instead of sequential integers:

```php
use App\Models\Traits\HasUid;

class User extends Model
{
    use HasUid;
}

// In your code
$user->uid;                     // "K4x9Pq" (not "1")
User::findByUid('K4x9Pq');      // Works
route('users.show', $user);     // Uses UID automatically

// In routes - just works
Route::get('/users/{user}', ...);  // Binds via UID
```

Configure alphabet/length in `config/sqids.php` for unique IDs per project.

## Generator Commands

Claudavel adds artisan commands that generate consistent, well-structured code:

```bash
# Create an Action (business logic)
php artisan make:action User/UpdateProfile
php artisan make:action Order/ApproveOrder

# Create a DTO with properties
php artisan make:dto UserProfile --properties=id:int,name:string,email:?string
php artisan make:dto OrderData --properties=id:int,total:int --model=Order

# Generate TypeScript interfaces from all DTOs
php artisan types:generate
```

### TypeScript Generation

The `types:generate` command scans `app/DataTransferObjects/` and creates TypeScript interfaces:

```typescript
// resources/js/types/generated.d.ts (auto-generated)
export interface UserProfileData {
    id: number;
    name: string;
    email?: string;
}
```

Import in your React components:

```tsx
import type { UserProfileData } from '@/types/generated';
```

Run `php artisan types:generate` after modifying DTOs to keep types in sync.

## Coding Standards

Claudavel publishes `docs/standards/` with conventions that AI assistants actually follow:

- **Actions pattern** - Business logic in `app/Actions/{Domain}/`
- **Money as integers** - Store cents, not dollars
- **Lowercase imports** - Prevents Linux build failures
- **createQuietly in tests** - Avoids event side effects

These aren't documentation theatre. They're the minimal set of rules that prevent real bugs.

## GitHub Workflows

Claudavel sets up a complete CI/CD pipeline:

| Workflow | Trigger | Purpose |
| -------- | ------- | ------- |
| tests.yml | Push/PR to main | Runs Pest with Postgres |
| lint.yml | Push/PR to main | Pint, ESLint, Prettier checks |
| claude-code-review.yml | PR opened | AI reviews code for issues |
| claude.yml | @claude mention | AI responds to issue/PR comments |
| dependabot-automerge.yml | Dependabot PR | Auto-merges minor/patch updates |

### Setup

Add `CLAUDE_CODE_OAUTH_TOKEN` to your repository secrets for Claude integration.

Use `--no-workflows` if you don't want these installed.

## Health Check

The `/health` endpoint verifies:

- Database connection
- Redis connection
- Cache read/write
- Queue dispatch
- Storage write

Use it for load balancer health checks and deploy verification.

## Requirements

- PHP 8.3+
- Laravel 12+
- Postgres (local or remote)
- Redis (local or remote)

## License

MIT
