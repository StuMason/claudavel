# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.0] - 2026-01-14

### Added
- Git workflow and slice-based development documentation in CLAUDE.md
- Controller → Job → Action async pattern documentation
- Reverb broadcasting documentation with useEcho hook examples
- gh CLI usage documentation for GitHub interactions
- Reference to `docs/standards/` for coding standards

### Fixed
- SQLite database file no longer created during installation (#12)
- Type generator now correctly maps `array<string, mixed>` to `Record<string, unknown>` (#17)

### Changed
- Updated GitHub Actions stubs to v6 (checkout, setup-node)
- Claude code review now only runs on PR open (reduces API usage)

## [1.4.0] - 2026-01-06

### Changed
- `--all` is now the default behavior (installs Horizon, Reverb, and Telescope)
- Added scheduler to `composer run dev` command

## [1.3.0] - 2026-01-06

### Added
- README rewrite with clearer documentation
- `.test` domain URLs in post-install output (for Herd/Valet users)

### Fixed
- Install command is now idempotent (can be run multiple times safely)
- Laravel 12 compatibility fixes

## [1.2.0] - 2026-01-06

### Fixed
- Install command idempotency improvements

## [1.1.0] - 2026-01-06

### Fixed
- Laravel 12 compatibility for install command

## [1.0.0] - 2026-01-05

### Added
- Initial release
- `claudavel:install` command with full stack setup
- Fortify, Sanctum, Horizon, Reverb, Telescope, Pail, Wayfinder integration
- Sqids for ID obfuscation with HasUid trait
- GitHub workflows: CI, Claude code review, Claude mentions, dependabot automerge
- Coding standards in `docs/standards/`
- `make:action` and `make:dto` commands
- `types:generate` for TypeScript generation from PHP DTOs
- Health check endpoint

[1.5.0]: https://github.com/StuMason/claudavel/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/StuMason/claudavel/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/StuMason/claudavel/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/StuMason/claudavel/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/StuMason/claudavel/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/StuMason/claudavel/releases/tag/v1.0.0
