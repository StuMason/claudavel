<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any test artifacts
    $this->testPath = sys_get_temp_dir().'/claudavel-test-'.uniqid();
    File::makeDirectory($this->testPath, 0755, true);
});

afterEach(function () {
    if (isset($this->testPath) && File::isDirectory($this->testPath)) {
        File::deleteDirectory($this->testPath);
    }
});

test('command is registered', function () {
    $this->artisan('claudavel:install --help')
        ->assertSuccessful();
});

test('command has correct signature', function () {
    $command = $this->app->make(\Stumason\Claudavel\Commands\InstallCommand::class);

    expect($command->getName())->toBe('claudavel:install');
    expect($command->getDescription())->toContain('opinionated Laravel setup');
});

test('stubs directory exists', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    expect(File::isDirectory($stubsPath))->toBeTrue();
});

test('all required stubs exist', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    $requiredStubs = [
        'HealthCheckController.php.stub',
        'health-check.tsx.stub',
        'HorizonServiceProvider.php.stub',
        'TelescopeServiceProvider.php.stub',
        'CLAUDE.md.stub',
        'prettierrc.stub',
        'prettierignore.stub',
        'editorconfig.stub',
        'eslint.config.js.stub',
        'docs/standards/README.md',
        'docs/standards/general.md',
        'docs/standards/backend.md',
        'docs/standards/frontend.md',
        'docs/standards/testing.md',
        'app/Models/Traits/HasUid.php.stub',
        'app/Services/SqidService.php.stub',
        'config/sqids.php.stub',
        'app/Actions/.gitkeep.stub',
        'app/DataTransferObjects/.gitkeep.stub',
    ];

    foreach ($requiredStubs as $stub) {
        expect(File::exists("{$stubsPath}/{$stub}"))->toBeTrue("Missing stub: {$stub}");
    }
});

test('health check controller stub is valid php', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/HealthCheckController.php.stub");

    expect($content)->toContain('namespace App\Http\Controllers;');
    expect($content)->toContain('class HealthCheckController');
    expect($content)->toContain('public function __invoke()');
});

test('service provider stubs have TODO for gate', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    $horizonContent = File::get("{$stubsPath}/HorizonServiceProvider.php.stub");
    expect($horizonContent)->toContain('TODO: Lock this down before production');

    $telescopeContent = File::get("{$stubsPath}/TelescopeServiceProvider.php.stub");
    expect($telescopeContent)->toContain('TODO: Lock this down before production');
});

test('HasUid stub is valid php', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/app/Models/Traits/HasUid.php.stub");

    expect($content)->toContain('namespace App\Models\Traits;');
    expect($content)->toContain('trait HasUid');
    expect($content)->toContain('getUidAttribute');
    expect($content)->toContain('SqidService');
    expect($content)->toContain('findByUid');
});

test('SqidService stub is valid php', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/app/Services/SqidService.php.stub");

    expect($content)->toContain('namespace App\Services;');
    expect($content)->toContain('class SqidService');
    expect($content)->toContain('Sqids\Sqids');
    expect($content)->toContain('public function encode');
    expect($content)->toContain('public function decode');
});

test('sqids config stub is valid php', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/config/sqids.php.stub");

    expect($content)->toContain("'alphabet'");
    expect($content)->toContain("'length'");
    expect($content)->toContain('env(');
});

test('coding standards are concise', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/docs/standards';

    // Backend standards should be under 200 lines (trimmed from 600+)
    $backendLines = count(explode("\n", File::get("{$stubsPath}/backend.md")));
    expect($backendLines)->toBeLessThan(200);

    // Frontend standards should be under 100 lines
    $frontendLines = count(explode("\n", File::get("{$stubsPath}/frontend.md")));
    expect($frontendLines)->toBeLessThan(100);

    // Testing standards should be under 100 lines
    $testingLines = count(explode("\n", File::get("{$stubsPath}/testing.md")));
    expect($testingLines)->toBeLessThan(100);
});
