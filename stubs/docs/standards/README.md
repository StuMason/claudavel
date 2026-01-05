# Coding Standards

> Stack: Laravel 12, Inertia.js v2, React 19, shadcn/ui, Pest v4, Tailwind CSS v4

---

## Quick Navigation

- [General Standards](./general.md) - Git workflow, core principles
- [Backend Standards](./backend.md) - Laravel patterns
- [Frontend Standards](./frontend.md) - Inertia + React patterns
- [Testing Standards](./testing.md) - Pest patterns

---

## Key Principles

1. **Branch, PR, review, merge** - Never commit directly to main
2. **Thin controllers, fat Actions** - Controllers orchestrate, Actions contain business logic
3. **Always import classes** - Never use inline FQCN
4. **Never use env() outside config files** - Use `config()` helper
5. **Type hints everywhere** - All parameters and return types
6. **Money as integers** - Store pence/cents, not pounds/dollars
7. **Lowercase import paths** - Critical for production builds on Linux
8. **Use createQuietly in tests** - Avoid event side effects
