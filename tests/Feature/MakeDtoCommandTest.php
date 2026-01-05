<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->dtoPath = app_path('DataTransferObjects');

    if (File::isDirectory($this->dtoPath)) {
        File::deleteDirectory($this->dtoPath);
    }
});

afterEach(function () {
    if (File::isDirectory($this->dtoPath)) {
        File::deleteDirectory($this->dtoPath);
    }
});

test('command is registered', function () {
    $this->artisan('make:dto --help')
        ->assertSuccessful();
});

test('creates dto with Data suffix', function () {
    $this->artisan('make:dto', ['name' => 'UserProfile', '--no-interaction' => true])
        ->assertSuccessful();

    // Should have appended Data
    expect(File::exists(app_path('DataTransferObjects/UserProfileData.php')))->toBeTrue();
});

test('does not double-suffix Data', function () {
    $this->artisan('make:dto', ['name' => 'UserData', '--no-interaction' => true])
        ->assertSuccessful();

    expect(File::exists(app_path('DataTransferObjects/UserData.php')))->toBeTrue();
    expect(File::exists(app_path('DataTransferObjects/UserDataData.php')))->toBeFalse();
});

test('creates dto with properties option', function () {
    $this->artisan('make:dto', [
        'name' => 'UserData',
        '--properties' => 'id:int,name:string,email:string',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $content = File::get(app_path('DataTransferObjects/UserData.php'));

    expect($content)->toContain('public int $id,');
    expect($content)->toContain('public string $name,');
    expect($content)->toContain('public string $email,');
});

test('creates dto with nullable properties', function () {
    $this->artisan('make:dto', [
        'name' => 'ProfileData',
        '--properties' => 'id:int,bio:?string',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $content = File::get(app_path('DataTransferObjects/ProfileData.php'));

    expect($content)->toContain('public int $id,');
    expect($content)->toContain('public ?string $bio,');
});

test('creates dto with model factory method', function () {
    $this->artisan('make:dto', [
        'name' => 'UserData',
        '--properties' => 'id:int,name:string',
        '--model' => 'User',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $content = File::get(app_path('DataTransferObjects/UserData.php'));

    expect($content)->toContain('public static function fromModel(User $model): self');
    expect($content)->toContain('id: $model->id,');
    expect($content)->toContain('name: $model->name,');
});

test('creates readonly class', function () {
    $this->artisan('make:dto', ['name' => 'TestData', '--no-interaction' => true])
        ->assertSuccessful();

    $content = File::get(app_path('DataTransferObjects/TestData.php'));

    expect($content)->toContain('final readonly class TestData');
});

test('refuses to overwrite without force flag', function () {
    File::ensureDirectoryExists(app_path('DataTransferObjects'));
    File::put(app_path('DataTransferObjects/UserData.php'), '<?php // existing');

    $this->artisan('make:dto', ['name' => 'UserData', '--no-interaction' => true])
        ->assertFailed();
});

test('overwrites with force flag', function () {
    File::ensureDirectoryExists(app_path('DataTransferObjects'));
    File::put(app_path('DataTransferObjects/UserData.php'), '<?php // existing');

    $this->artisan('make:dto', [
        'name' => 'UserData',
        '--force' => true,
        '--no-interaction' => true,
    ])->assertSuccessful();

    $content = File::get(app_path('DataTransferObjects/UserData.php'));
    expect($content)->toContain('final readonly class UserData');
});

test('dto has proper namespace', function () {
    $this->artisan('make:dto', ['name' => 'OrderData', '--no-interaction' => true])
        ->assertSuccessful();

    $content = File::get(app_path('DataTransferObjects/OrderData.php'));

    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('namespace App\DataTransferObjects;');
});
