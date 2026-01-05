<?php

declare(strict_types=1);

namespace Stumason\Claudavel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action
                            {name : The name of the action (e.g., User/UpdateProfile or UpdateProfile)}
                            {--force : Overwrite existing file}';

    protected $description = 'Create a new Action class following the Actions pattern';

    public function handle(): int
    {
        $name = $this->argument('name');
        $name = str_replace('/', '\\', $name);

        // Parse domain and class name
        if (str_contains($name, '\\')) {
            $parts = explode('\\', $name);
            $className = array_pop($parts);
            $domain = implode('\\', $parts);
            $namespace = "App\\Actions\\{$domain}";
            $directory = app_path('Actions/'.str_replace('\\', '/', $domain));
        } else {
            $className = $name;
            $namespace = 'App\\Actions';
            $domain = '';
            $directory = app_path('Actions');
        }

        // Ensure class name ends with Action pattern if it doesn't follow convention
        if (! Str::startsWith($className, ['Create', 'Update', 'Delete', 'Get', 'List', 'Toggle', 'Send', 'Process', 'Calculate', 'Validate', 'Mark', 'Approve', 'Reject', 'Publish'])) {
            warning('Consider prefixing with a verb (Create, Update, Delete, etc.) for clarity.');
        }

        $filePath = "{$directory}/{$className}.php";

        if (File::exists($filePath) && ! $this->option('force')) {
            warning("Action already exists: {$filePath}");
            warning('Use --force to overwrite.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($directory);

        $stub = $this->generateStub($namespace, $className);
        File::put($filePath, $stub);

        info("Action created: {$filePath}");

        if ($domain) {
            $this->components->bulletList([
                "Namespace: {$namespace}",
                "Usage: app({$className}::class)->handle(...)",
            ]);
        }

        return self::SUCCESS;
    }

    private function generateStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class {$className}
{
    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  \$data
     * @throws InvalidArgumentException
     */
    public function handle(array \$data): mixed
    {
        return DB::transaction(function () use (\$data) {
            // TODO: Implement action logic
            //
            // Example:
            // \$model->update([
            //     'field' => \$data['field'],
            // ]);
            //
            // SomeEvent::dispatch(\$model);
            //
            // return \$model;
        });
    }
}

PHP;
    }
}
