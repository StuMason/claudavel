<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->actionsPath = app_path('Actions');

    // Clean up before each test
    if (File::isDirectory($this->actionsPath)) {
        File::deleteDirectory($this->actionsPath);
    }
});

afterEach(function () {
    // Clean up after each test
    if (File::isDirectory($this->actionsPath)) {
        File::deleteDirectory($this->actionsPath);
    }
});

test('command is registered', function () {
    $this->artisan('make:action --help')
        ->assertSuccessful();
});

test('creates action in root Actions directory', function () {
    $this->artisan('make:action', ['name' => 'CreateUser'])
        ->assertSuccessful();

    $filePath = app_path('Actions/CreateUser.php');

    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('namespace App\Actions;');
    expect($content)->toContain('class CreateUser');
    expect($content)->toContain('public function handle(array $data): mixed');
    expect($content)->toContain('DB::transaction');
});

test('creates action in domain subdirectory', function () {
    $this->artisan('make:action', ['name' => 'User/UpdateProfile'])
        ->assertSuccessful();

    $filePath = app_path('Actions/User/UpdateProfile.php');

    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('namespace App\Actions\User;');
    expect($content)->toContain('class UpdateProfile');
});

test('creates action with nested domain path', function () {
    $this->artisan('make:action', ['name' => 'Admin/User/BanUser'])
        ->assertSuccessful();

    $filePath = app_path('Actions/Admin/User/BanUser.php');

    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('namespace App\Actions\Admin\User;');
});

test('refuses to overwrite existing action without force flag', function () {
    File::ensureDirectoryExists(app_path('Actions'));
    File::put(app_path('Actions/CreateUser.php'), '<?php // existing');

    $this->artisan('make:action', ['name' => 'CreateUser'])
        ->assertFailed();
});

test('overwrites existing action with force flag', function () {
    File::ensureDirectoryExists(app_path('Actions'));
    File::put(app_path('Actions/CreateUser.php'), '<?php // existing');

    $this->artisan('make:action', ['name' => 'CreateUser', '--force' => true])
        ->assertSuccessful();

    $content = File::get(app_path('Actions/CreateUser.php'));
    expect($content)->toContain('class CreateUser');
});

test('generated action has proper structure', function () {
    $this->artisan('make:action', ['name' => 'Order/ApproveOrder'])
        ->assertSuccessful();

    $content = File::get(app_path('Actions/Order/ApproveOrder.php'));

    // Check for required components
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('use Illuminate\Support\Facades\DB;');
    expect($content)->toContain('use InvalidArgumentException;');
    expect($content)->toContain('@throws InvalidArgumentException');
    expect($content)->toContain('@param  array<string, mixed>  $data');
});

test('accepts forward slashes in name', function () {
    $this->artisan('make:action', ['name' => 'User/Settings/UpdateNotifications'])
        ->assertSuccessful();

    expect(File::exists(app_path('Actions/User/Settings/UpdateNotifications.php')))->toBeTrue();
});
