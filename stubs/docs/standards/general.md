# General Coding Standards

---

## Always Import Classes

```php
// Good
use Illuminate\Support\Facades\Auth;
Auth::logout();

// Bad
\Illuminate\Support\Facades\Auth::logout();
```

---

## Never Use env() Outside Config Files

In production, `env()` returns `null` after `config:cache`.

```php
// Good - config file (config/admin.php)
return [
    'emails' => explode(',', env('ADMIN_EMAILS', '')),
];

// Good - application code
$emails = config('admin.emails', []);

// Bad - application code
$emails = env('ADMIN_EMAILS');
```

---

## Use $throwable Not $e

```php
try {
    // ...
} catch (Throwable $throwable) {
    throw ValidationException::withMessages([
        'error' => $throwable->getMessage(),
    ]);
}
```

---

## Type Hints Everywhere

```php
// Good
public function handle(User $user, array $data): bool

// Bad
public function handle($user, $data)
```
