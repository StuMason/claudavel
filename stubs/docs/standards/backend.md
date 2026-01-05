# Backend Coding Standards

---

## Money Stored as Integers

Store pence/cents to avoid floating point issues.

```php
// Migration
$table->integer('amount'); // Stores cents

// Display (frontend)
(amount / 100).toFixed(2)
```

---

## Actions Pattern

Business logic goes in `app/Actions/{Domain}/`. Controllers just orchestrate.

```php
// app/Actions/User/UpdateUserProfile.php
class UpdateUserProfile
{
    public function handle(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return $user;
    }
}

// Controller - thin, just orchestrates
public function update(UpdateProfileRequest $request, UpdateUserProfile $action): RedirectResponse
{
    try {
        $action->handle($request->user(), $request->validated());
    } catch (InvalidArgumentException $throwable) {
        return back()->with('error', $throwable->getMessage());
    }

    return back()->with('success', 'Updated');
}
```

---

## HasUid Trait for ID Obfuscation

Use the `HasUid` trait on models to expose obfuscated IDs via Sqids.

```php
use App\Models\Traits\HasUid;

class User extends Model
{
    use HasUid;
}

// Usage
$user->uid;                    // Returns obfuscated ID like "K4x9Pq"
User::findByUid('K4x9Pq');     // Find by UID
route('users.show', $user);    // Routes use UID automatically
```

---

## DTOs for Inertia Data

Use DTOs for explicit, type-safe data passed to Inertia pages.

```php
final readonly class UserProfileData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
        );
    }
}

// Controller
return Inertia::render('Profile', [
    'user' => UserProfileData::fromModel($user),
]);
```

---

## Modern Casts Method

Use the `casts()` method, not the `$casts` property.

```php
// Good
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'status' => UserStatus::class,
    ];
}

// Bad
protected $casts = ['email_verified_at' => 'datetime'];
```

---

## Soft Deletes for User Entities

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
}
```

---

## Pivot Tables Need ID Column

Required for `HasOneOfMany` queries.

```php
Schema::create('role_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('role_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});
```
