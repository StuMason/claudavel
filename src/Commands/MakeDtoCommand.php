<?php

declare(strict_types=1);

namespace Stumason\Claudavel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto
                            {name : The name of the DTO (e.g., UserProfileData)}
                            {--model= : Generate fromModel method for this model}
                            {--properties= : Comma-separated properties (e.g., id:int,name:string,email:string)}
                            {--force : Overwrite existing file}';

    protected $description = 'Create a new Data Transfer Object class';

    public function handle(): int
    {
        $name = $this->argument('name');

        // Ensure name ends with Data
        if (! Str::endsWith($name, 'Data')) {
            $name .= 'Data';
            info("Renamed to {$name} (DTOs should end with 'Data')");
        }

        $filePath = app_path("DataTransferObjects/{$name}.php");

        if (File::exists($filePath) && ! $this->option('force')) {
            warning("DTO already exists: {$filePath}");
            warning('Use --force to overwrite.');

            return self::FAILURE;
        }

        $model = $this->option('model');
        $propertiesOption = $this->option('properties');

        // Parse properties
        $properties = [];
        if ($propertiesOption) {
            foreach (explode(',', $propertiesOption) as $prop) {
                $parts = explode(':', trim($prop));
                $rawType = $parts[1] ?? 'mixed';
                $nullable = str_starts_with($rawType, '?');
                $type = $nullable ? ltrim($rawType, '?') : $rawType;

                $properties[] = [
                    'name' => $parts[0],
                    'type' => $type,
                    'nullable' => $nullable,
                ];
            }
        }

        // If no properties provided and interactive, prompt for them
        if (empty($properties) && ! $this->option('no-interaction')) {
            $propertiesInput = text(
                label: 'Properties (comma-separated, e.g., id:int,name:string,email:string)',
                placeholder: 'id:int,name:string',
                required: false
            );

            if ($propertiesInput) {
                foreach (explode(',', $propertiesInput) as $prop) {
                    $parts = explode(':', trim($prop));
                    $rawType = $parts[1] ?? 'mixed';
                    $nullable = str_starts_with($rawType, '?');
                    $type = $nullable ? ltrim($rawType, '?') : $rawType;

                    $properties[] = [
                        'name' => $parts[0],
                        'type' => $type,
                        'nullable' => $nullable,
                    ];
                }
            }
        }

        // Default properties if none provided
        if (empty($properties)) {
            $properties = [
                ['name' => 'id', 'type' => 'int', 'nullable' => false],
            ];
        }

        File::ensureDirectoryExists(app_path('DataTransferObjects'));

        $stub = $this->generateStub($name, $properties, $model);
        File::put($filePath, $stub);

        info("DTO created: {$filePath}");

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{name: string, type: string, nullable: bool}>  $properties
     */
    private function generateStub(string $className, array $properties, ?string $model): string
    {
        $constructorParams = [];
        foreach ($properties as $prop) {
            $type = $prop['nullable'] ? "?{$prop['type']}" : $prop['type'];
            $constructorParams[] = "        public {$type} \${$prop['name']},";
        }
        $constructorBody = implode("\n", $constructorParams);

        $fromModelMethod = '';
        if ($model) {
            $modelClass = class_exists("App\\Models\\{$model}") ? $model : $model;
            $modelAssignments = [];
            foreach ($properties as $prop) {
                $modelAssignments[] = "            {$prop['name']}: \$model->{$prop['name']},";
            }
            $assignments = implode("\n", $modelAssignments);

            $fromModelMethod = <<<PHP


    public static function fromModel({$modelClass} \$model): self
    {
        return new self(
{$assignments}
        );
    }
PHP;
        }

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class {$className}
{
    public function __construct(
{$constructorBody}
    ) {}
{$fromModelMethod}
}

PHP;
    }
}
