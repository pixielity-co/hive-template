# PHP Turborepo Monorepo

A production-ready PHP monorepo powered by Turborepo, pnpm, and Composer.

## Structure

```
.
├── apps/
│   └── api/              # Laravel 12 application
├── packages/
│   ├── calculator/       # PHP library package
│   └── logger/           # PHP library package
├── tooling/
│   ├── schemas/          # Templates for new apps/packages
│   ├── phpstan/          # PHPStan configurations
│   ├── pint/             # Pint (PHP CS Fixer) config
│   ├── rector/           # Rector refactoring config
│   ├── infection/        # Infection mutation testing config
│   ├── linting/          # Prettier config for non-PHP files
│   └── scripts/          # Workspace management scripts
├── bin/
│   └── mono              # PHP CLI wrapper for common tasks
├── composer.json         # Root composer with advanced tooling
├── package.json          # Root npm scripts
├── turbo.json            # Turborepo pipeline configuration
└── pnpm-workspace.yaml   # pnpm workspace definition
```

## Quick Start

```bash
# Install all dependencies (node + composer)
pnpm install

# Or use the PHP CLI
php bin/mono install

# Start development
pnpm dev

# Run tests
pnpm test

# Lint code
pnpm lint

# Format code
pnpm format

# Type check
pnpm typecheck
```

## The `mono` CLI

A PHP binary that wraps pnpm/turbo for a native PHP developer experience:

```bash
# Development
php bin/mono dev                    # Start all dev servers
php bin/mono dev --filter=api       # Start only api

# Testing
php bin/mono test                   # Run all tests
php bin/mono test --filter=api      # Test only api

# Code Quality
php bin/mono lint                   # Check code style
php bin/mono format                 # Fix code style
php bin/mono typecheck              # Run static analysis
php bin/mono refactor               # Run Rector refactoring
php bin/mono refactor:dry           # Preview Rector changes
php bin/mono mutate                 # Run mutation testing

# Utilities
php bin/mono artisan migrate        # Run artisan commands
php bin/mono composer api require laravel/sanctum
php bin/mono workspace:sync         # Check workspace health
php bin/mono clean                  # Clear caches
php bin/mono cleanup                # Nuclear clean (removes all deps)
```

## Architecture

### Centralized Tooling

All complex tooling logic lives at the root level:

- **Root `composer.json`**: Handles Rector, Infection, and orchestrates lint/format/typecheck across workspaces
- **Root `package.json`**: Delegates to Turbo for parallel execution
- **Turbo**: Manages task dependencies, caching, and parallelization

### Simple Workspaces

Apps and packages have minimal, consistent scripts:

**Apps** (`apps/*/package.json`):
```json
{
  "scripts": {
    "dev": "php artisan serve",
    "test": "vendor/bin/phpunit",
    "lint": "vendor/bin/pint --test",
    "format": "vendor/bin/pint",
    "typecheck": "vendor/bin/phpstan analyse",
    "clean": "php artisan optimize:clear && rm -rf .phpstan.cache",
    "composer:install": "composer install --no-interaction --prefer-dist --optimize-autoloader"
  }
}
```

**Packages** (`packages/*/package.json`):
```json
{
  "scripts": {
    "test": "vendor/bin/phpunit",
    "lint": "vendor/bin/pint --test",
    "format": "vendor/bin/pint",
    "typecheck": "vendor/bin/phpstan analyse",
    "clean": "rm -rf vendor .phpstan.cache",
    "composer:install": "composer install --no-interaction --prefer-dist --optimize-autoloader"
  }
}
```

## Tooling Stack

- **Pint**: Code formatting (Laravel's PHP CS Fixer wrapper)
- **PHPStan**: Static analysis at level 8
- **Larastan**: PHPStan for Laravel (apps only)
- **Rector**: Automated refactoring (PHP 8.2, code quality, dead code removal)
- **Infection**: Mutation testing
- **PHPUnit**: Unit and feature testing
- **Prettier**: Formatting for JSON, YAML, Markdown

## Task Pipeline

Turbo orchestrates tasks with proper dependencies:

```
composer:install (cached)
    ↓
lint / format / test / typecheck (parallel)
    ↓
build
    ↓
deploy
```

### Caching

- `composer:install`: Caches `vendor/` and `composer.lock`
- `typecheck`: Caches `.phpstan.cache/`
- `test`: Caches test datasets
- `build`: Caches `public/build/`

## Creating New Workspaces

Use the schema templates in `tooling/schemas/`:

### New App

```bash
mkdir -p apps/admin
cp tooling/schemas/composer-app.json apps/admin/composer.json
cp tooling/schemas/package-app.json apps/admin/package.json
# Edit files to update name, namespace, etc.
cd apps/admin && composer install
```

### New Package

```bash
mkdir -p packages/utils
cp tooling/schemas/composer-package.json packages/utils/composer.json
cp tooling/schemas/package-package.json packages/utils/package.json
# Edit files to update name, namespace, etc.
cd packages/utils && composer install
```

## Using Local Packages

Apps automatically discover all local packages via wildcard path repository:

```json
// apps/api/composer.json
{
  "repositories": [
    { "type": "path", "url": "../../packages/*" }
  ],
  "require": {
    "mono-php/calculator": "*",
    "mono-php/logger": "*"
  }
}
```

When you add a new package to `packages/`, just require it in your app and run `composer update`.

## Scripts Reference

### Root Scripts (pnpm)

| Script | Description |
|--------|-------------|
| `pnpm install` | Install all deps (triggers postinstall → composer:install) |
| `pnpm dev` | Start all dev servers |
| `pnpm build` | Build all apps |
| `pnpm test` | Run all tests |
| `pnpm lint` | Check code style |
| `pnpm format` | Fix code style |
| `pnpm typecheck` | Run static analysis |
| `pnpm refactor` | Run Rector refactoring |
| `pnpm mutate` | Run mutation testing |
| `pnpm clean` | Clear caches |
| `pnpm cleanup` | Nuclear clean (removes all deps) |
| `pnpm deploy` | Full deploy pipeline |

### Root Scripts (composer)

| Script | Description |
|--------|-------------|
| `composer lint` | Lint all workspaces |
| `composer format` | Format all workspaces |
| `composer typecheck` | Type check all workspaces |
| `composer refactor` | Run Rector across all workspaces |
| `composer refactor:dry` | Preview Rector changes |
| `composer mutate` | Run Infection across all workspaces |
| `composer test` | Run tests across all workspaces |

## Configuration Files

- `turbo.json`: Task pipeline and caching rules
- `composer.json`: Root-level PHP tooling
- `tooling/rector/rector.php`: Rector configuration
- `tooling/phpstan/*.neon`: PHPStan configurations
- `tooling/pint/pint.json`: Pint preset
- `tooling/infection/infection.json5`: Mutation testing config
- `tooling/linting/.prettierrc`: Prettier config

## CI/CD

The monorepo is designed for CI/CD pipelines:

```yaml
# Example GitHub Actions
- run: pnpm install
- run: pnpm lint
- run: pnpm typecheck
- run: pnpm test
- run: pnpm build
```

Turbo's caching works with CI providers (Vercel, GitHub Actions, etc.) for remote caching.

## Dependency Management

### Composer

- **`vendor/`**: Ignored in git, generated by `composer install`
- **`composer.lock`**: Committed to git for reproducible builds
- Each workspace has its own `vendor/` and `composer.lock`
- Root also has `vendor/` and `composer.lock` for shared tooling (Rector, Infection)

### npm/pnpm

- **`node_modules/`**: Ignored in git, generated by `pnpm install`
- **`pnpm-lock.yaml`**: Committed to git for reproducible builds
- Single root `node_modules/` (pnpm workspace hoisting)

### Clean Commands

```bash
# Clear caches only (keeps dependencies and lock files)
pnpm clean

# Nuclear clean (removes EVERYTHING including lock files)
pnpm cleanup
# This removes:
# - node_modules/ (all)
# - vendor/ (all)
# - .turbo/ (all)
# - .phpstan.cache/ (all)
# - pnpm-lock.yaml
# - composer.lock (all)
# - *.log (all)
```

**⚠️ Warning:** After `pnpm cleanup`, you'll need to run `pnpm install` to regenerate lock files and reinstall all dependencies.

## Requirements

- PHP 8.2+
- Node.js 18+
- Composer 2.x
- pnpm 9.x

## License

MIT
