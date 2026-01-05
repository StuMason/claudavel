# Frontend Coding Standards

---

## Lowercase Import Paths (Critical)

Production builds on Linux are case-sensitive. Always use lowercase.

```tsx
// Good
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

// Bad - will break production
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/app-layout';
```

---

## Wayfinder for Routes

Use generated route helpers, never hardcode URLs.

```tsx
import { edit } from '@/routes/profile';

// Good
<Link href={edit().url}>Edit Profile</Link>

// Bad
<Link href="/settings/profile">Edit Profile</Link>
```

---

## Inertia Form Component

```tsx
import { Form } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

<Form
    {...ProfileController.update.form()}
    options={{ preserveScroll: true }}
>
    {({ processing, errors }) => (
        <>
            <Input name="name" defaultValue={auth.user.name} />
            <InputError message={errors.name} />
            <Button disabled={processing}>Save</Button>
        </>
    )}
</Form>
```

---

## Money Display

Money is stored as integers (pence/cents). Divide for display.

```tsx
<p>{(amount / 100).toFixed(2)}</p>
```

---

## File Naming

- Component files: kebab-case (`app-header.tsx`)
- Component functions: PascalCase (`export default function AppHeader()`)

---

## Icons

Use `lucide-react`. Never emojis in UI.

```tsx
import { Search } from 'lucide-react';

<Button>
    <Search className="size-4" />
    Search
</Button>
```
