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
        'config/claudavel.php.stub',
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

test('service provider stubs have secure gates', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    $horizonContent = File::get("{$stubsPath}/HorizonServiceProvider.php.stub");
    expect($horizonContent)->toContain("app()->environment('local')");
    expect($horizonContent)->toContain("config('claudavel.admin_emails'");

    $telescopeContent = File::get("{$stubsPath}/TelescopeServiceProvider.php.stub");
    expect($telescopeContent)->toContain("app()->environment('local')");
    expect($telescopeContent)->toContain("config('claudavel.admin_emails'");
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

test('claudavel config stub is valid php', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/config/claudavel.php.stub");

    expect($content)->toContain("'admin_emails'");
    expect($content)->toContain('ADMIN_EMAILS');
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

test('sanitizeName converts project names correctly', function () {
    $command = new \Stumason\Claudavel\Commands\InstallCommand;

    expect($command->sanitizeName('My Project'))->toBe('my_project');
    expect($command->sanitizeName('my-cool-app'))->toBe('my_cool_app');
    expect($command->sanitizeName('CamelCaseApp'))->toBe('camelcaseapp');
    expect($command->sanitizeName('app_with_underscores'))->toBe('app_with_underscores');
    expect($command->sanitizeName('App With Spaces & Symbols!'))->toBe('app_with_spaces___symbols_');
    expect($command->sanitizeName('123-numeric-start'))->toBe('123_numeric_start');
});

test('command has all expected options', function () {
    $command = $this->app->make(\Stumason\Claudavel\Commands\InstallCommand::class);
    $definition = $command->getDefinition();

    expect($definition->hasOption('horizon'))->toBeTrue();
    expect($definition->hasOption('reverb'))->toBeTrue();
    expect($definition->hasOption('telescope'))->toBeTrue();
    expect($definition->hasOption('no-workflows'))->toBeTrue();
    expect($definition->hasOption('force'))->toBeTrue();
    expect($definition->hasArgument('name'))->toBeTrue();
});

test('stubs contain valid php syntax', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    $phpStubs = [
        'HealthCheckController.php.stub',
        'HorizonServiceProvider.php.stub',
        'TelescopeServiceProvider.php.stub',
        'app/Models/Traits/HasUid.php.stub',
        'app/Services/SqidService.php.stub',
        'config/sqids.php.stub',
    ];

    foreach ($phpStubs as $stub) {
        $content = File::get("{$stubsPath}/{$stub}");
        // Check for PHP opening tag and namespace/return
        expect($content)->toStartWith('<?php');
        // Check for no obvious syntax errors (balanced braces)
        $opens = substr_count($content, '{');
        $closes = substr_count($content, '}');
        expect($opens)->toBe($closes, "Unbalanced braces in {$stub}");
    }
});

test('CLAUDE.md stub contains required sections', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/CLAUDE.md.stub");

    expect($content)->toContain('Running Tests');
    expect($content)->toContain('php artisan test');
    expect($content)->toContain('docs/standards');
});

test('CLAUDE.md stub contains git workflow documentation', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/CLAUDE.md.stub");

    expect($content)->toContain('## Git Workflow');
    expect($content)->toContain('Slice-Based Development');
    expect($content)->toContain('feature/');
});

test('CLAUDE.md stub contains gh CLI documentation', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/CLAUDE.md.stub");

    expect($content)->toContain('## GitHub Interactions');
    expect($content)->toContain('gh issue');
    expect($content)->toContain('gh pr');
});

test('CLAUDE.md stub contains async job pattern documentation', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/CLAUDE.md.stub");

    expect($content)->toContain('## Async Job Pattern');
    expect($content)->toContain('Controller');
    expect($content)->toContain('Job');
    expect($content)->toContain('Action');
    expect($content)->toContain('ShouldQueue');
});

test('CLAUDE.md stub contains Reverb broadcasting documentation', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';
    $content = File::get("{$stubsPath}/CLAUDE.md.stub");

    expect($content)->toContain('## Real-Time Updates with Reverb');
    expect($content)->toContain('useEcho');
    expect($content)->toContain('ShouldBroadcast');
    expect($content)->toContain('PrivateChannel');
});

test('config stubs are properly formatted', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    // Prettierrc should be valid JSON
    $prettierContent = File::get("{$stubsPath}/prettierrc.stub");
    $prettierJson = json_decode($prettierContent, true);
    expect($prettierJson)->not->toBeNull('prettierrc.stub is not valid JSON');

    // Editorconfig should have root directive
    $editorconfigContent = File::get("{$stubsPath}/editorconfig.stub");
    expect($editorconfigContent)->toContain('root = true');
});

test('service provider registers command', function () {
    $commands = \Illuminate\Support\Facades\Artisan::all();

    expect($commands)->toHaveKey('claudavel:install');
});

test('all workflow stubs exist', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs';

    $requiredWorkflows = [
        '.github/workflows/tests.yml.stub',
        '.github/workflows/lint.yml.stub',
        '.github/workflows/claude-code-review.yml.stub',
        '.github/workflows/claude.yml.stub',
        '.github/workflows/dependabot-automerge.yml.stub',
        '.github/dependabot.yml.stub',
    ];

    foreach ($requiredWorkflows as $workflow) {
        expect(File::exists("{$stubsPath}/{$workflow}"))->toBeTrue("Missing workflow stub: {$workflow}");
    }
});

test('workflow stubs are valid yaml', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';

    $workflows = [
        'tests.yml.stub',
        'lint.yml.stub',
        'claude-code-review.yml.stub',
        'claude.yml.stub',
        'dependabot-automerge.yml.stub',
    ];

    foreach ($workflows as $workflow) {
        $content = File::get("{$stubsPath}/{$workflow}");
        // Basic YAML validation - check for name key and on trigger
        expect($content)->toContain('name:');
        expect($content)->toContain('on:');
        expect($content)->toContain('jobs:');
    }
});

test('dependabot config is valid yaml', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github';
    $content = File::get("{$stubsPath}/dependabot.yml.stub");

    expect($content)->toContain('version: 2');
    expect($content)->toContain('updates:');
    expect($content)->toContain('package-ecosystem: composer');
    expect($content)->toContain('package-ecosystem: npm');
    expect($content)->toContain('package-ecosystem: github-actions');
});

test('tests workflow has postgres setup', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';
    $content = File::get("{$stubsPath}/tests.yml.stub");

    expect($content)->toContain('Setup PostgreSQL');
    expect($content)->toContain('pdo_pgsql');
    expect($content)->toContain('vendor/bin/pest');
});

test('workflow stubs use current action versions', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';

    // Test the tests.yml.stub
    $testsContent = File::get("{$stubsPath}/tests.yml.stub");
    expect($testsContent)->toContain('actions/checkout@v6');
    expect($testsContent)->toContain('actions/setup-node@v6');

    // Test the lint.yml.stub
    $lintContent = File::get("{$stubsPath}/lint.yml.stub");
    expect($lintContent)->toContain('actions/checkout@v6');
    expect($lintContent)->toContain('actions/setup-node@v6');

    // Test the claude.yml.stub
    $claudeContent = File::get("{$stubsPath}/claude.yml.stub");
    expect($claudeContent)->toContain('actions/checkout@v6');

    // Test the claude-code-review.yml.stub
    $reviewContent = File::get("{$stubsPath}/claude-code-review.yml.stub");
    expect($reviewContent)->toContain('actions/checkout@v6');
});

test('lint workflow checks pint and eslint', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';
    $content = File::get("{$stubsPath}/lint.yml.stub");

    expect($content)->toContain('vendor/bin/pint');
    expect($content)->toContain('npm run lint');
    expect($content)->toContain('npm run format:check');
});

test('claude workflows have access controls', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';

    $claudeReview = File::get("{$stubsPath}/claude-code-review.yml.stub");
    expect($claudeReview)->toContain("github.actor != 'dependabot[bot]'");

    // claude.yml uses author_association for security
    $claude = File::get("{$stubsPath}/claude.yml.stub");
    expect($claude)->toContain("author_association == 'OWNER'");
    expect($claude)->toContain("author_association == 'COLLABORATOR'");
});

test('claude workflows use oauth token', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';

    $claudeReview = File::get("{$stubsPath}/claude-code-review.yml.stub");
    expect($claudeReview)->toContain('CLAUDE_CODE_OAUTH_TOKEN');

    $claude = File::get("{$stubsPath}/claude.yml.stub");
    expect($claude)->toContain('CLAUDE_CODE_OAUTH_TOKEN');
});

test('dependabot automerge workflow handles minor and patch', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/workflows';
    $content = File::get("{$stubsPath}/dependabot-automerge.yml.stub");

    expect($content)->toContain('dependabot/fetch-metadata');
    expect($content)->toContain('semver-minor');
    expect($content)->toContain('semver-patch');
    expect($content)->toContain('gh pr merge --auto --squash');
});

test('command has no-workflows option', function () {
    $command = $this->app->make(\Stumason\Claudavel\Commands\InstallCommand::class);
    $definition = $command->getDefinition();

    expect($definition->hasOption('no-workflows'))->toBeTrue();
});

test('github templates stubs exist', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github';

    expect(File::exists("{$stubsPath}/CODEOWNERS.stub"))->toBeTrue();
    expect(File::exists("{$stubsPath}/PULL_REQUEST_TEMPLATE.md.stub"))->toBeTrue();
    expect(File::exists("{$stubsPath}/ISSUE_TEMPLATE/bug_report.yml.stub"))->toBeTrue();
    expect(File::exists("{$stubsPath}/ISSUE_TEMPLATE/config.yml.stub"))->toBeTrue();
    expect(File::exists("{$stubsPath}/ISSUE_TEMPLATE/feature_request.yml.stub"))->toBeTrue();
});

test('issue templates are valid yaml', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github/ISSUE_TEMPLATE';

    $bugReport = File::get("{$stubsPath}/bug_report.yml.stub");
    expect($bugReport)->toContain('name: Bug Report');
    expect($bugReport)->toContain('labels:');
    expect($bugReport)->toContain('body:');

    $featureRequest = File::get("{$stubsPath}/feature_request.yml.stub");
    expect($featureRequest)->toContain('name: Feature Request');
    expect($featureRequest)->toContain('labels:');
    expect($featureRequest)->toContain('body:');

    $config = File::get("{$stubsPath}/config.yml.stub");
    expect($config)->toContain('blank_issues_enabled:');
});

test('pull request template has checklist', function () {
    $stubsPath = dirname(__DIR__, 2).'/stubs/.github';
    $content = File::get("{$stubsPath}/PULL_REQUEST_TEMPLATE.md.stub");

    expect($content)->toContain('## Summary');
    expect($content)->toContain('## Changes');
    expect($content)->toContain('## Test plan');
    expect($content)->toContain('## Checklist');
    expect($content)->toContain('docs/standards/');
});
