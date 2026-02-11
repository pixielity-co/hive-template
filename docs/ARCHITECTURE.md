# Architecture Overview

## Design Philosophy

This monorepo follows a **centralized orchestration, decentralized execution** pattern:

- **Root level**: Handles complex tooling, orchestration, and cross-workspace operations
- **Workspaces**: Simple, consistent interfaces with minimal configuration
- **Turbo**: Manages task dependencies, parallelization, and caching

## Key Principles

### 1. Centralized Tooling

All advanced tooling (Rector, Infection, complex flags) lives in the root `composer.json`:

```json
{
  "require-dev": {
    "infection/infection": "^0.29",
    "rector/rector": "^2.0"
  },
  "scripts": {
    "refactor": "vendor/bin/rector process",
    "mutate": "cd apps/api && vendor/bin/infection --threads=4 --min-msi=50"
  }
}
```

**Why?** 
- Single source of truth for tooling versions
- Complex flags and options in one place
- Easy to update tooling across all workspaces
- Workspaces stay simple and maintainable

### 2. Simple Workspace Contracts

Every workspace implements the same minimal script interface:

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

**Why?**
- Predictable interface across all workspaces
- Easy to add new apps/packages (copy template, done)
- Turbo can orchestrate consistently
- Developers know what scripts are available

### 3. Turbo Orchestration

Turbo manages the task graph and execution:

```json
{
  "tasks": {
    "lint": {
      "dependsOn": ["^lint", "composer:install"]
    }
  }
}
```

**Why?**
- Parallel execution where possible
- Proper dependency ordering (composer:install before lint)
- Intelligent caching (skip unchanged workspaces)
- Works across multiple languages (PHP, JS, Go, etc.)

## Task Flow

### Example: `pnpm lint`

1. User runs `pnpm lint`
2. Root `package.json` delegates to `turbo run lint`
3. Turbo analyzes the task graph:
   - Finds all workspaces with a `lint` script
   - Checks if `composer:install` needs to run first
   - Determines which workspaces changed (cache invalidation)
4. Turbo executes:
   - `composer:install` in parallel across workspaces (if needed)
   - `lint` in parallel across workspaces (after composer:install)
5. Results are cached for next run

### Example: `pnpm refactor`

1. User runs `pnpm refactor`
2. Root `package.json` delegates to `composer refactor`
3. Root `composer.json` runs `vendor/bin/rector process`
4. Rector uses `tooling/rector/rector.php` config
5. Rector processes all paths defined in config

**Why not Turbo?** Rector needs to see the entire codebase at once for cross-file refactoring. Running it per-workspace would miss opportunities.

## Directory Structure

```
.
├── apps/                    # Applications (Laravel, etc.)
│   └── api/
│       ├── composer.json    # Simple: just dependencies
│       ├── package.json     # Simple: just script interface
│       ├── phpstan.neon     # Workspace-specific config
│       └── rector.php       # Workspace-specific config
│
├── packages/                # Shared libraries
│   ├── calculator/
│   │   ├── composer.json    # Simple: just dependencies
│   │   ├── package.json     # Simple: just script interface
│   │   └── phpstan.neon     # Workspace-specific config
│   └── logger/
│
├── tooling/                 # Shared configurations
│   ├── schemas/             # Templates for new workspaces
│   ├── phpstan/             # Shared PHPStan configs
│   ├── pint/                # Shared Pint config
│   ├── rector/              # Shared Rector config
│   ├── infection/           # Shared Infection config
│   ├── linting/             # Prettier config
│   └── scripts/             # Workspace management
│
├── bin/
│   └── mono                 # PHP CLI wrapper
│
├── composer.json            # Root: advanced tooling
├── package.json             # Root: orchestration
└── turbo.json               # Task graph
```

## Configuration Hierarchy

### PHPStan

- `tooling/phpstan/phpstan.neon`: Base config for Laravel apps
- `tooling/phpstan/phpstan-package.neon`: Base config for packages
- `apps/api/phpstan.neon`: App-specific overrides (includes Larastan)
- `packages/*/phpstan.neon`: Package-specific overrides

### Rector

- `tooling/rector/rector.php`: Global config (all workspaces)
- `apps/api/rector.php`: App-specific config (optional)
- `packages/*/rector.php`: Package-specific config (optional)

### Pint

- `tooling/pint/pint.json`: Global preset
- Workspaces can override with local `pint.json` if needed

## Composer Workspace Pattern

### Path Repositories

Apps use wildcard path repositories to auto-discover all local packages:

```json
// apps/api/composer.json
{
  "repositories": [
    { "type": "path", "url": "../../packages/*" }
  ],
  "require": {
    "mono-php/calculator": "*"
  }
}
```

**How it works:**
- Composer scans `packages/*` for any package with a `composer.json`
- In dev: Composer symlinks `packages/calculator` → `apps/api/vendor/mono-php/calculator`
- In prod: Composer copies the package (or uses a real package registry)
- Adding a new package? Just create it in `packages/` and require it — no config changes needed

### Independent Installs

Each workspace runs its own `composer install`:
- `apps/api/vendor/` — Laravel + dependencies
- `packages/calculator/vendor/` — Pint, PHPStan, PHPUnit
- `packages/logger/vendor/` — Pint, PHPStan, PHPUnit

**Why not merge?** 
- Cleaner dependency isolation
- Turbo can cache each workspace independently
- Easier to extract packages to separate repos later

## Caching Strategy

### Turbo Cache

Turbo caches task outputs based on inputs:

| Task | Inputs | Outputs | Cache? |
|------|--------|---------|--------|
| `composer:install` | `composer.json`, `composer.lock` | `vendor/**`, `composer.lock` | ✅ |
| `lint` | All source files | None | ✅ |
| `format` | All source files | Modified files | ❌ |
| `test` | All source + test files | `tests/Datasets/**` | ✅ |
| `typecheck` | All source files, `phpstan.neon` | `.phpstan.cache/**` | ✅ |
| `build` | All source files, `.env*` | `public/build/**` | ✅ |
| `dev` | N/A | N/A | ❌ (persistent) |

### Remote Caching

Turbo supports remote caching via Vercel or custom backends:

```bash
turbo login
turbo link
```

Now CI and local dev share the same cache.

## Adding New Workspaces

### 1. Copy Templates

```bash
# New app
mkdir -p apps/admin
cp tooling/schemas/composer-app.json apps/admin/composer.json
cp tooling/schemas/package-app.json apps/admin/package.json

# New package
mkdir -p packages/utils
cp tooling/schemas/composer-package.json packages/utils/composer.json
cp tooling/schemas/package-package.json packages/utils/package.json
```

### 2. Customize

Edit the copied files:
- Update `name` fields
- Update `autoload` namespaces
- Add dependencies

### 3. Install

```bash
cd apps/admin && composer install
# or
pnpm install  # postinstall hook runs composer:install everywhere
```

### 4. Update Root Composer (Optional)

If you want root-level scripts to include the new workspace:

```json
// composer.json
{
  "scripts": {
    "lint:apps": [
      "cd apps/api && vendor/bin/pint --test",
      "cd apps/admin && vendor/bin/pint --test"
    ]
  }
}
```

## The `mono` CLI

A PHP binary that provides a native PHP developer experience:

```php
// bin/mono
#!/usr/bin/env php
<?php

// Wraps pnpm/turbo commands
// Provides shortcuts for common tasks
// Handles workspace-specific operations
```

**Why?** PHP developers shouldn't need to learn npm/pnpm/turbo. They should use familiar PHP tools.

## CI/CD Integration

### GitHub Actions Example

```yaml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: 18
      
      - name: Install pnpm
        run: npm install -g pnpm
      
      - name: Install dependencies
        run: pnpm install
      
      - name: Lint
        run: pnpm lint
      
      - name: Type check
        run: pnpm typecheck
      
      - name: Test
        run: pnpm test
      
      - name: Build
        run: pnpm build
```

### Turbo Remote Cache

```yaml
      - name: Setup Turbo
        run: |
          turbo login --token=${{ secrets.TURBO_TOKEN }}
          turbo link
```

## Benefits

### For Developers

- Simple, consistent interface across all workspaces
- Fast feedback (Turbo caching)
- Native PHP CLI (`bin/mono`)
- Easy to add new workspaces (copy templates)

### For Teams

- Centralized tooling configuration
- Enforced consistency (same scripts everywhere)
- Easy to update tooling (change root, done)
- Clear separation of concerns

### For CI/CD

- Fast builds (Turbo caching)
- Parallel execution
- Only rebuild what changed
- Remote cache sharing

## Trade-offs

### Pros

- ✅ Simple workspace configs
- ✅ Centralized tooling
- ✅ Fast execution (parallel + cache)
- ✅ Easy to maintain
- ✅ Scales to many workspaces

### Cons

- ❌ Requires understanding Turbo
- ❌ Root composer.json can get large (many workspace-specific scripts)
- ❌ Some tools (Rector, Infection) run globally, not per-workspace

### When to Use This Pattern

- ✅ Multiple PHP apps/packages in one repo
- ✅ Shared tooling across workspaces
- ✅ Need fast CI/CD
- ✅ Team wants consistency

### When NOT to Use This Pattern

- ❌ Single app (no need for monorepo)
- ❌ Workspaces have wildly different tooling needs
- ❌ Team prefers per-workspace autonomy
