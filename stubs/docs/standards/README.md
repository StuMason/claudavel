# Coding Standards

> Stack: Laravel 12, Inertia.js v2, React 19, shadcn/ui, Pest v4, Tailwind CSS v4

---

## Quick Navigation

- [General Standards](./general.md) - Core principles
- [Backend Standards](./backend.md) - Laravel patterns
- [Frontend Standards](./frontend.md) - Inertia + React patterns
- [Testing Standards](./testing.md) - Pest patterns

---

## Key Principles

1. **Thin controllers, fat Actions** - Controllers orchestrate, Actions contain business logic
2. **Always import classes** - NEVER use inline FQCN
3. **Never use env() outside config files** - Use `config()` helper
4. **Type hints everywhere** - All parameters and return types
5. **Money as integers** - Store pence/cents, not pounds/dollars
6. **Lowercase import paths** - Critical for production builds on Linux
7. **Use createQuietly in tests** - Avoid event side effects
