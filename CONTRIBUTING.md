# Contributing Guide

## Git Strategy

### What Gets Committed

✅ **Source code**
- `apps/*/app/`, `apps/*/config/`, `apps/*/routes/`, etc.
- `packages/*/src/`
- `tooling/` configs

✅ **Lock files** (for reproducible builds)
- `composer.lock` (root and all workspaces)
- `pnpm-lock.yaml` (root only)

✅ **Configuration**
- `composer.json` (root and all workspaces)
- `package.json` (root and all workspaces)
- `turbo.json`
- `phpstan.neon`, `rector.php`, etc.

✅ **Documentation**
- `README.md`, `ARCHITECTURE.md`, etc.

### What Gets Ignored

❌ **Dependencies**
- `vendor/` (all workspaces)
- `node_modules/` (root)

❌ **Caches**
- `.turbo/`
- `.phpstan.cache/`
- `.phpunit.cache/`

❌ **Build artifacts**
- `public/build/`
- `dist/`, `out/`, `build/`

❌ **Logs**
- `*.log`
- `.infection.log`, `.infection-summary.log`

❌ **Environment files**
- `.env` (use `.env.example` as template)

❌ **IDE files**
- `.vscode/`, `.idea/`, etc. (personal preference)

## Workflow

### Initial Setup

```bash
# Clone the repo
git clone <repo-url>
cd mono-php

# Install all dependencies
pnpm install
# This triggers postinstall → composer:install across all workspaces

# Verify everything works
pnpm test
```

### Daily Development

```bash
# Start dev servers
pnpm dev

# Run tests
pnpm test

# Check code style
pnpm lint

# Fix code style
pnpm format

# Type check
pnpm typecheck
```

### Before Committing

```bash
# Format code
pnpm format

# Run full checks
pnpm lint
pnpm typecheck
pnpm test

# Or use the deploy pipeline
pnpm deploy
```

### Adding Dependencies

**To a specific workspace:**
```bash
# Using mono CLI
php bin/mono composer api require laravel/sanctum

# Or directly
cd apps/api && composer require laravel/sanctum
```

**To root (shared tooling):**
```bash
composer require --dev some/tool
```

**Node dependencies:**
```bash
# Workspace-specific (rare in PHP monorepo)
pnpm add -w some-package

# Root-level
pnpm add -D some-package
```

### Creating New Workspaces

**New App:**
```bash
# 1. Create directory structure
mkdir -p apps/my-app/{app,config,routes,tests}

# 2. Copy and customize composer.json from apps/api
cp apps/api/composer.json apps/my-app/composer.json
# Edit: name, autoload namespaces, dependencies

# 3. Copy and customize package.json from apps/api
cp apps/api/package.json apps/my-app/package.json
# Edit: name

# 4. Install dependencies
cd apps/my-app && composer install
```

**New Package:**
```bash
# 1. Create directory structure
mkdir -p packages/my-package/{src,tests}

# 2. Copy and customize from existing package
cp packages/calculator/composer.json packages/my-package/composer.json
cp packages/calculator/package.json packages/my-package/package.json
# Edit: name, autoload namespaces

# 3. Install dependencies
cd packages/my-package && composer install
```

### Using Local Packages

Apps automatically discover all local packages via wildcard path repository:

```json
// apps/api/composer.json
{
  "repositories": [
    { "type": "path", "url": "../../packages/*" }
  ],
  "require": {
    "mono-php/my-package": "*"
  }
}
```

Then run:
```bash
cd apps/api && composer update mono-php/my-package
```

Composer will symlink the package in dev.

## Troubleshooting

### "Class not found" errors

```bash
# Regenerate autoloader
cd apps/api && composer dump-autoload
```

### Turbo cache issues

```bash
# Clear Turbo cache only
pnpm clean

# Nuclear clean (removes all deps and lock files)
pnpm cleanup
# Then reinstall
pnpm install
```

### Composer dependency conflicts

```bash
# Update all dependencies
cd apps/api && composer update

# Or just one
cd apps/api && composer update vendor/package
```

### PHPStan errors after adding code

```bash
# Clear PHPStan cache
rm -rf apps/api/.phpstan.cache
pnpm typecheck
```

## Code Style

- **PHP**: Laravel conventions, enforced by Pint
- **Strict types**: All PHP files should have `declare(strict_types=1);`
- **PHPStan level 8**: All code should pass static analysis
- **Tests**: PHPUnit for unit and feature tests

## Pull Request Checklist

- [ ] Code formatted (`pnpm format`)
- [ ] Linting passes (`pnpm lint`)
- [ ] Type checking passes (`pnpm typecheck`)
- [ ] Tests pass (`pnpm test`)
- [ ] New tests added for new features
- [ ] `composer.lock` and `pnpm-lock.yaml` committed if dependencies changed
- [ ] Documentation updated if needed

## Questions?

Check the documentation:
- `README.md` — Quick start and usage
- `ARCHITECTURE.md` — Design decisions and patterns
