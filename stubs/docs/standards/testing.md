# Testing Standards

---

## Use createQuietly

Suppresses model events to avoid side effects.

```php
// Good
$user = User::factory()->createQuietly();

// Bad - triggers events
$user = User::factory()->create();
```

---

## Fake Events Not Being Tested

```php
use Illuminate\Support\Facades\Event;

it('creates a category', function () {
    Event::fake([CategoryCreated::class]);
    $user = User::factory()->createQuietly();

    $response = $this->actingAs($user)->post(route('categories.store'), [
        'name' => 'Test',
    ]);

    $response->assertRedirect();
});
```

---

## Test Actions Directly

```php
test('it updates user profile', function () {
    $user = User::factory()->create();

    $action = new UpdateUserProfile;
    $action->handle($user, [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->fresh()->name)->toBe('John Doe');
});
```

---

## AAA Structure

Arrange, Act, Assert.

```php
it('updates profile', function () {
    // Arrange
    $user = User::factory()->create(['name' => 'Old']);

    // Act
    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'New',
    ]);

    // Assert
    expect($user->fresh()->name)->toBe('New');
});
```

---

## Test Order

1. Authorization tests
2. Validation tests
3. Happy path tests

---

## Use Datasets

```php
it('validates email format', function (string $email) {
    $response = $this->post(route('register'), ['email' => $email]);
    $response->assertSessionHasErrors(['email']);
})->with([
    'not-an-email',
    'missing@domain',
]);
```
