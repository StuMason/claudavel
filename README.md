# Claudavel

Sets up a fresh Laravel app with my opinionated setup.

## What this does

Runs `php artisan claudavel:install` which:

- Installs core packages: Fortify, Sanctum, Pail, Wayfinder, Sqids
- Installs optional packages: Horizon, Reverb, Telescope (prompts for each)
- Runs `php artisan install:api` and `install:broadcasting`
- Adds a `/health` endpoint that checks database, cache, redis, queue, storage
- Switches `.env` to Postgres and Redis
- Adds a `composer run dev` script that runs all services concurrently
- Publishes HasUid trait + SqidService for ID obfuscation
- Publishes coding standards docs to `docs/standards/`
- Publishes config files (.prettierrc, .editorconfig, eslint.config.js)
- Creates `app/Actions/` and `app/DataTransferObjects/` directories
- Updates CLAUDE.md with coding standards references

## Requirements

- PHP 8.3+
- Laravel 12+
- Fresh Laravel install (with Inertia + React stack)
- Postgres and Redis running locally

## Installation

```bash
composer require stumason/claudavel --dev
php artisan claudavel:install
```

Answer the prompts or use flags:

```bash
php artisan claudavel:install --all                    # Install everything
php artisan claudavel:install --horizon --reverb       # Pick specific packages
php artisan claudavel:install --no-interaction         # Install all, skip prompts
php artisan claudavel:install --force                  # Overwrite existing files
php artisan claudavel:install my-project-name          # Custom database name
```

## After installation

```bash
createdb your_project_name    # Create the postgres database
composer run dev              # Start all services
```

Visit:

- `/` - Your app
- `/health` - System health checks
- `/horizon` - Queue management (if installed)
- `/telescope` - Debugging (if installed)

## Files published

```
app/Http/Controllers/HealthCheckController.php
app/Models/Traits/HasUid.php
app/Services/SqidService.php
app/Actions/.gitkeep
app/DataTransferObjects/.gitkeep
app/Providers/HorizonServiceProvider.php      (if Horizon)
app/Providers/TelescopeServiceProvider.php    (if Telescope)
config/sqids.php
resources/js/pages/health-check.tsx
docs/standards/
.prettierrc
.prettierignore
.editorconfig
eslint.config.js
CLAUDE.md (prepended)
```

## HasUid trait

Obfuscates model IDs using Sqids:

```php
use App\Models\Traits\HasUid;

class User extends Model
{
    use HasUid;
}

$user->uid;                    // "K4x9Pq"
User::findByUid('K4x9Pq');     // Find by UID
route('users.show', $user);    // Routes use UID automatically
```

Configure in `config/sqids.php`:

```php
'alphabet' => env('SQIDS_ALPHABET'),  // Custom alphabet for uniqueness
'length' => env('SQIDS_LENGTH', 8),   // Minimum UID length
```

## .env changes

```
APP_NAME="Your Project Name"
DB_CONNECTION=pgsql
DB_DATABASE=your_project_name
DB_HOST=127.0.0.1
DB_PORT=5432
DB_USERNAME=postgres
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REVERB_APP_ID=...       (if Reverb)
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
```

## composer.json changes

Adds a `dev` script that runs all services concurrently:

```json
{
    "scripts": {
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74,#4ade80\" \"php artisan serve\" \"php artisan horizon\" \"php artisan reverb:start\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,horizon,reverb,logs,vite --kill-others"
        ]
    }
}
```

## License

MIT
