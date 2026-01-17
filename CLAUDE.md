# CLAUDE.md

Instructions for Claude Code when working on this repository.

## Build & Test Commands

```bash
composer install          # Install dependencies
composer test             # Run test suite
composer lint             # Run code linting (Pint)
```

## GitHub Capabilities

You have full access to the `gh` CLI and can perform GitHub operations directly:

```bash
# Create a pull request
gh pr create --title "Title" --body "Description"

# Create PR that closes an issue
gh pr create --title "Title" --body "Closes #123"
```

**You CAN and SHOULD create PRs directly** using `gh pr create` when you've made changes. Don't just provide URLs - actually create the PR.

## Code Style

This is a Laravel package. Follow Laravel conventions:
- PSR-12 coding style
- Type hints on all parameters and return types
- DocBlocks only where they add value beyond type hints
