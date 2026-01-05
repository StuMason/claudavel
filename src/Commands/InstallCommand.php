<?php

namespace Stumason\Claudavel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class InstallCommand extends Command
{
    protected $signature = 'claudavel:install
                            {name? : Project name for database naming}
                            {--horizon : Install Laravel Horizon}
                            {--reverb : Install Laravel Reverb}
                            {--telescope : Install Laravel Telescope}
                            {--all : Install all optional packages}
                            {--no-workflows : Skip GitHub workflows installation}
                            {--force : Overwrite existing files}';

    protected $description = 'Install Claudavel: opinionated Laravel setup with Fortify, Horizon, Reverb, Telescope, UIDs, and coding standards';

    private bool $installHorizon = false;

    private bool $installReverb = false;

    private bool $installTelescope = false;

    private string $projectName = '';

    public function handle(): int
    {
        info('Installing Claudavel...');

        $this->determineProjectName();
        $this->determinePackagesToInstall();
        $this->installPackages();
        $this->runArtisanInstallCommands();
        $this->publishStubs();
        $this->updateComposerJson();
        $this->updateBootstrapProviders();
        $this->updateEnvFile();
        $this->runMigrations();

        $this->newLine();
        info('Claudavel installed successfully!');
        $this->newLine();

        $this->components->bulletList(array_filter([
            'Run <comment>composer run dev</comment> to start the development server',
            'Visit <comment>/health</comment> to check system status',
            $this->installHorizon ? 'Visit <comment>/horizon</comment> to monitor queues' : null,
            $this->installTelescope ? 'Visit <comment>/telescope</comment> for debugging' : null,
        ]));

        $this->newLine();
        $this->components->warn('Remember to add HasUid trait to your models for ID obfuscation');

        return self::SUCCESS;
    }

    private function determineProjectName(): void
    {
        $name = $this->argument('name');

        if ($name) {
            $this->projectName = $this->sanitizeName($name);

            return;
        }

        $this->projectName = $this->sanitizeName(basename(base_path()));
    }

    public function sanitizeName(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
    }

    private function determinePackagesToInstall(): void
    {
        if ($this->option('all')) {
            $this->installHorizon = true;
            $this->installReverb = true;
            $this->installTelescope = true;

            return;
        }

        if ($this->option('horizon') || $this->option('reverb') || $this->option('telescope')) {
            $this->installHorizon = $this->option('horizon');
            $this->installReverb = $this->option('reverb');
            $this->installTelescope = $this->option('telescope');

            return;
        }

        if ($this->option('no-interaction')) {
            $this->installHorizon = true;
            $this->installReverb = true;
            $this->installTelescope = true;

            return;
        }

        $this->installHorizon = confirm(
            label: 'Install Laravel Horizon? (Redis-based queue management)',
            default: true
        );

        $this->installReverb = confirm(
            label: 'Install Laravel Reverb? (WebSocket server)',
            default: true
        );

        $this->installTelescope = confirm(
            label: 'Install Laravel Telescope? (Debugging & monitoring)',
            default: true
        );
    }

    private function installPackages(): void
    {
        // Core packages (always installed)
        $packages = [
            'laravel/fortify',
            'laravel/sanctum',
            'laravel/pail',
            'laravel/wayfinder',
            'sqids/sqids',
        ];

        // Optional packages
        if ($this->installHorizon) {
            $packages[] = 'laravel/horizon';
        }

        if ($this->installReverb) {
            $packages[] = 'laravel/reverb';
        }

        if ($this->installTelescope) {
            $packages[] = 'laravel/telescope';
        }

        $packageList = implode(' ', $packages);

        spin(
            callback: function () use ($packageList) {
                Process::run("composer require {$packageList} --no-interaction")->throw();
            },
            message: 'Installing Composer packages...'
        );

        // Publish package assets
        spin(
            callback: fn () => Process::run('php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider" --no-interaction')->throw(),
            message: 'Publishing Fortify assets...'
        );

        if ($this->installHorizon) {
            spin(
                callback: fn () => Process::run('php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider" --no-interaction')->throw(),
                message: 'Publishing Horizon assets...'
            );
        }

        if ($this->installReverb) {
            spin(
                callback: fn () => Process::run('php artisan vendor:publish --provider="Laravel\Reverb\ReverbServiceProvider" --no-interaction')->throw(),
                message: 'Publishing Reverb assets...'
            );
        }

        if ($this->installTelescope) {
            spin(
                callback: fn () => Process::run('php artisan vendor:publish --provider="Laravel\Telescope\TelescopeServiceProvider" --no-interaction')->throw(),
                message: 'Publishing Telescope assets...'
            );
        }
    }

    private function runArtisanInstallCommands(): void
    {
        // Install API routes (creates routes/api.php, configures Sanctum)
        spin(
            callback: fn () => Process::run('php artisan install:api --without-migration-prompt --no-interaction')->throw(),
            message: 'Setting up API routes...'
        );

        // Install broadcasting (creates routes/channels.php)
        if ($this->installReverb) {
            spin(
                callback: fn () => Process::run('php artisan install:broadcasting --without-reverb --without-node --no-interaction')->throw(),
                message: 'Setting up broadcasting...'
            );
        }
    }

    private function publishStubs(): void
    {
        $stubsPath = dirname(__DIR__, 2).'/stubs';
        $force = $this->option('force');

        // UID system (SqidService + HasUid trait + config)
        $this->publishFile(
            "{$stubsPath}/app/Services/SqidService.php.stub",
            app_path('Services/SqidService.php'),
            'SqidService',
            $force
        );

        $this->publishFile(
            "{$stubsPath}/app/Models/Traits/HasUid.php.stub",
            app_path('Models/Traits/HasUid.php'),
            'HasUid trait',
            $force
        );

        $this->publishFile(
            "{$stubsPath}/config/sqids.php.stub",
            config_path('sqids.php'),
            'config/sqids.php',
            $force
        );

        // Create Actions and DTOs directories
        File::ensureDirectoryExists(app_path('Actions'));
        File::ensureDirectoryExists(app_path('DataTransferObjects'));
        info('Created app/Actions and app/DataTransferObjects directories');

        // Health check
        $this->publishFile(
            "{$stubsPath}/HealthCheckController.php.stub",
            app_path('Http/Controllers/HealthCheckController.php'),
            'HealthCheckController',
            $force
        );

        $this->publishFile(
            "{$stubsPath}/health-check.tsx.stub",
            resource_path('js/pages/health-check.tsx'),
            'health-check.tsx',
            $force
        );

        // Service providers
        if ($this->installHorizon) {
            $this->publishFile(
                "{$stubsPath}/HorizonServiceProvider.php.stub",
                app_path('Providers/HorizonServiceProvider.php'),
                'HorizonServiceProvider',
                $force
            );
        }

        if ($this->installTelescope) {
            $this->publishFile(
                "{$stubsPath}/TelescopeServiceProvider.php.stub",
                app_path('Providers/TelescopeServiceProvider.php'),
                'TelescopeServiceProvider',
                $force
            );
        }

        // Coding standards
        $this->publishDirectory(
            "{$stubsPath}/docs/standards",
            base_path('docs/standards'),
            'docs/standards',
            $force
        );

        // Config files
        $this->publishFile(
            "{$stubsPath}/prettierrc.stub",
            base_path('.prettierrc'),
            '.prettierrc',
            $force
        );

        $this->publishFile(
            "{$stubsPath}/prettierignore.stub",
            base_path('.prettierignore'),
            '.prettierignore',
            $force
        );

        $this->publishFile(
            "{$stubsPath}/editorconfig.stub",
            base_path('.editorconfig'),
            '.editorconfig',
            $force
        );

        $this->publishFile(
            "{$stubsPath}/eslint.config.js.stub",
            base_path('eslint.config.js'),
            'eslint.config.js',
            $force
        );

        // Update CLAUDE.md
        $this->prependToClaudeMd("{$stubsPath}/CLAUDE.md.stub");

        // Update AppServiceProvider for Telescope
        if ($this->installTelescope) {
            $this->updateAppServiceProvider();
        }

        // Add routes
        $this->addHealthCheckRoute();

        // GitHub workflows
        if (! $this->option('no-workflows')) {
            $this->publishWorkflows($stubsPath, $force);
        }
    }

    private function publishFile(string $source, string $destination, string $name, bool $force): void
    {
        if (File::exists($destination) && ! $force) {
            warning("Skipping {$name} (already exists). Use --force to overwrite.");

            return;
        }

        File::ensureDirectoryExists(dirname($destination));
        File::copy($source, $destination);
        info("Published {$name}");
    }

    private function publishDirectory(string $source, string $destination, string $name, bool $force): void
    {
        if (File::isDirectory($destination) && ! $force) {
            warning("Skipping {$name} (already exists). Use --force to overwrite.");

            return;
        }

        File::ensureDirectoryExists($destination);
        File::copyDirectory($source, $destination);
        info("Published {$name}");
    }

    private function updateEnvFile(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            warning('.env file not found, skipping environment setup.');

            return;
        }

        $content = File::get($envPath);
        $updates = [];

        if (str_contains($content, 'APP_NAME=Laravel')) {
            $appName = ucwords(str_replace('_', ' ', $this->projectName));
            $content = preg_replace('/^APP_NAME=.*/m', "APP_NAME=\"{$appName}\"", $content);
            $updates[] = 'APP_NAME';
        }

        if (str_contains($content, 'DB_CONNECTION=sqlite') || preg_match('/^#\s*DB_HOST=/m', $content)) {
            $dbSettings = [
                'DB_CONNECTION' => 'pgsql',
                'DB_HOST' => '127.0.0.1',
                'DB_PORT' => '5432',
                'DB_DATABASE' => $this->projectName,
                'DB_USERNAME' => 'postgres',
                'DB_PASSWORD' => '',
            ];

            foreach ($dbSettings as $key => $value) {
                $content = preg_replace(
                    '/^#?\s*'.preg_quote($key, '/').'=.*/m',
                    "{$key}={$value}",
                    $content
                );
            }
            $updates[] = 'DB_*';
        }

        $redisSettings = [
            'SESSION_DRIVER' => 'redis',
            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
        ];

        foreach ($redisSettings as $key => $value) {
            if (preg_match("/^{$key}=(?!{$value})/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                $updates[] = $key;
            }
        }

        if ($this->installReverb && ! str_contains($content, 'REVERB_APP_ID')) {
            $reverbId = random_int(100000, 999999);
            $reverbKey = bin2hex(random_bytes(16));
            $reverbSecret = bin2hex(random_bytes(16));

            $content .= "\n# Reverb WebSocket Server\n";
            $content .= "REVERB_APP_ID={$reverbId}\n";
            $content .= "REVERB_APP_KEY={$reverbKey}\n";
            $content .= "REVERB_APP_SECRET={$reverbSecret}\n";
            $content .= "REVERB_HOST=localhost\n";
            $content .= "REVERB_PORT=8080\n";
            $content .= "REVERB_SCHEME=http\n";
            $updates[] = 'REVERB_*';
        }

        if (! empty($updates)) {
            File::put($envPath, $content);
            info('Updated .env: '.implode(', ', $updates));

            Process::run('php artisan config:clear --no-interaction');
        }
    }

    private function prependToClaudeMd(string $stubPath): void
    {
        $claudeMdPath = base_path('CLAUDE.md');
        $stubContent = File::get($stubPath);

        if (File::exists($claudeMdPath)) {
            $existingContent = File::get($claudeMdPath);

            if (str_contains($existingContent, 'docs/standards/')) {
                warning('Skipping CLAUDE.md (already contains coding standards reference).');

                return;
            }

            File::put($claudeMdPath, $stubContent."\n\n---\n\n".$existingContent);
            info('Updated CLAUDE.md with coding standards references');
        } else {
            File::put($claudeMdPath, $stubContent);
            info('Published CLAUDE.md');
        }
    }

    private function updateAppServiceProvider(): void
    {
        $path = app_path('Providers/AppServiceProvider.php');
        $content = File::get($path);

        if (str_contains($content, 'TelescopeServiceProvider')) {
            return;
        }

        $content = str_replace(
            'use Illuminate\Support\ServiceProvider;',
            "use Illuminate\Support\ServiceProvider;\nuse Laravel\Telescope\TelescopeServiceProvider as BaseTelescopeServiceProvider;",
            $content
        );

        $registerMethod = <<<'PHP'
    public function register(): void
    {
        // Register Telescope only when Redis extension is available
        // This prevents build failures during package:discover
        if (extension_loaded('redis')) {
            $this->app->register(BaseTelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
PHP;

        $content = preg_replace(
            '/public function register\(\): void\s*\{[^}]*\}/s',
            $registerMethod,
            $content
        );

        File::put($path, $content);
        info('Updated AppServiceProvider for Telescope');
    }

    private function addHealthCheckRoute(): void
    {
        $routesPath = base_path('routes/web.php');
        $content = File::get($routesPath);

        if (str_contains($content, 'HealthCheckController')) {
            return;
        }

        if (! str_contains($content, 'use App\Http\Controllers\HealthCheckController;')) {
            $content = preg_replace(
                '/<\?php/',
                "<?php\n\nuse App\Http\Controllers\HealthCheckController;",
                $content,
                1
            );
        }

        $content .= "\n\nRoute::get('/health', HealthCheckController::class)->name('health');\n";

        File::put($routesPath, $content);
        info('Added /health route');
    }

    private function updateComposerJson(): void
    {
        $composerPath = base_path('composer.json');
        $composer = json_decode(File::get($composerPath), true);

        $devCommands = ['php artisan serve'];

        if ($this->installHorizon) {
            $devCommands[] = 'php artisan horizon';
        }

        if ($this->installReverb) {
            $devCommands[] = 'php artisan reverb:start';
        }

        $devCommands[] = 'php artisan pail --timeout=0';
        $devCommands[] = 'npm run dev';

        $colors = ['#93c5fd', '#c4b5fd', '#fb7185', '#fdba74', '#4ade80'];
        $names = ['server'];

        if ($this->installHorizon) {
            $names[] = 'horizon';
        }
        if ($this->installReverb) {
            $names[] = 'reverb';
        }
        $names[] = 'logs';
        $names[] = 'vite';

        $colorStr = implode(',', array_slice($colors, 0, count($names)));
        $nameStr = implode(',', $names);
        $cmdStr = implode('" "', $devCommands);

        $composer['scripts']['dev'] = [
            'Composer\\Config::disableProcessTimeout',
            "npx concurrently -c \"{$colorStr}\" \"{$cmdStr}\" --names={$nameStr} --kill-others",
        ];

        if ($this->installTelescope) {
            $composer['extra']['laravel']['dont-discover'] = $composer['extra']['laravel']['dont-discover'] ?? [];
            if (! in_array('laravel/telescope', $composer['extra']['laravel']['dont-discover'])) {
                $composer['extra']['laravel']['dont-discover'][] = 'laravel/telescope';
            }
        }

        File::put($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        info('Updated composer.json scripts');
    }

    private function updateBootstrapProviders(): void
    {
        $providersPath = base_path('bootstrap/providers.php');
        $content = File::get($providersPath);

        $providersToAdd = [];

        if ($this->installHorizon && ! str_contains($content, 'HorizonServiceProvider')) {
            $providersToAdd[] = 'App\\Providers\\HorizonServiceProvider::class';
        }

        if (empty($providersToAdd)) {
            return;
        }

        foreach ($providersToAdd as $provider) {
            $content = preg_replace(
                '/return \[(.*?)\];/s',
                "return [\$1    {$provider},\n];",
                $content
            );
        }

        File::put($providersPath, $content);
        info('Updated bootstrap/providers.php');
    }

    private function runMigrations(): void
    {
        spin(
            callback: function () {
                Process::run('php artisan migrate --force')->throw();
            },
            message: 'Running migrations...'
        );
        info('Migrations complete');
    }

    private function publishWorkflows(string $stubsPath, bool $force): void
    {
        $workflowsPath = base_path('.github/workflows');
        File::ensureDirectoryExists($workflowsPath);

        $workflows = [
            'tests.yml',
            'lint.yml',
            'claude-code-review.yml',
            'claude.yml',
            'dependabot-automerge.yml',
        ];

        foreach ($workflows as $workflow) {
            $this->publishFile(
                "{$stubsPath}/.github/workflows/{$workflow}.stub",
                "{$workflowsPath}/{$workflow}",
                ".github/workflows/{$workflow}",
                $force
            );
        }

        // Dependabot config goes in .github root
        $this->publishFile(
            "{$stubsPath}/.github/dependabot.yml.stub",
            base_path('.github/dependabot.yml'),
            '.github/dependabot.yml',
            $force
        );

        $this->newLine();
        $this->components->warn('GitHub workflows require CLAUDE_CODE_OAUTH_TOKEN secret for Claude integration');
    }
}
