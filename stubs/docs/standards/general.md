# General Coding Standards

---

## Git Workflow

Follow this workflow for all changes:

```bash
# 1. Start from latest main
git checkout main
git pull origin main

# 2. Create feature branch
git checkout -b feature/short-description

# 3. Do work, commit often
git add .
git commit -m "feat: add user profile endpoint"

# 4. Push and create PR
git push -u origin feature/short-description
gh pr create --fill

# 5. After review feedback, fix and push
git add .
git commit -m "fix: address review feedback"
git push

# 6. After approval, squash merge via GitHub UI or CLI
gh pr merge --squash --delete-branch
```

### Branch Naming

- `feature/` - New functionality
- `fix/` - Bug fixes
- `refactor/` - Code improvements
- `chore/` - Maintenance tasks

### Commit Messages

Use conventional commits:

- `feat:` - New feature
- `fix:` - Bug fix
- `refactor:` - Code change that neither fixes nor adds
- `test:` - Adding tests
- `chore:` - Maintenance

### Important

- Never commit directly to `main`
- Never force push to `main`
- Always create a PR for review
- Squash merge to keep history clean

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
