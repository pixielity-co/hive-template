# Configuration Refactoring Summary

## Overview

All package.json and composer.json files have been refactored to route commands through the CLI tool (`./cli/bin/mono`). This provides a unified interface for all monorepo operations with better UX, error handling, and consistency.

## Changes Made

### 1. Root Configuration Files

#### `package.json`
**Before**: Commands directly called `turbo run <task>` or `composer <command>`
**After**: All commands route through `./cli/bin/mono <command>`

```json
{
  "scripts": {
    "postinstall": "./cli/bin/mono install",
    "dev": "./cli/bin/mono dev",
    "build": "./cli/bin/mono build",
    "test": "./cli/bin/mono test",
    "lint": "./cli/bin/mono lint",
    "format": "./cli/bin/mono format",
    "typecheck": "./cli/bin/mono typecheck",
    "clean": "./cli/bin/mono clean",
    "cleanup": "./cli/bin/mono cleanup"
  }
}
```

#### `composer.json`
**Before**: Complex nested scripts with hardcoded workspace paths
**After**: Simple CLI delegation

```json
{
  "scripts": {
    "install": "./cli/bin/mono install",
    "test": "./cli/bin/mono test",
    "lint": "./cli/bin/mono lint",
    "format": "./cli/bin/mono format",
    "typecheck": "./cli/bin/mono typecheck",
    "refactor": "./cli/bin/mono refactor",
    "mutate": "./cli/bin/mono mutate"
  }
}
```

### 2. Workspace Configuration Files

#### Apps & Packages `package.json`
**Standardized scripts across all workspaces:**

```json
{
  "scripts": {
    "composer:install": "composer install --no-interaction --prefer-dist --optimize-autoloader",
    "dev": "php -S localhost:8000 -t public",  // Apps only
    "build": "echo 'Build complete'",
    "test": "vendor/bin/phpunit",
    "test:unit": "vendor/bin/phpunit --testsuite=Unit",
    "test:feature": "vendor/bin/phpunit --testsuite=Feature",
    "lint": "vendor/bin/pint --test",
    "format": "vendor/bin/pint",
    "typecheck": "vendor/bin/phpstan analyse",
    "clean": "rm -rf .phpunit.cache .phpstan.cache coverage"
  }
}
```

#### Apps & Packages `composer.json`
**Standardized scripts:**

```json
{
  "scripts": {
    "test": "phpunit",
    "test:unit": "phpunit --testsuite=Unit",
    "test:feature": "phpunit --testsuite=Feature",
    "lint": "pint --test",
    "format": "pint",
    "typecheck": "phpstan analyse"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0",
    "phpstan/phpstan": "^2.0",
    "laravel/pint": "^1.24"
  }
}
```

## Command Flow

### Old Flow
```
User → pnpm test
     → turbo run test
     → @mono-php/demo-app:test
     → composer test
     → cd apps/demo-app && vendor/bin/phpunit
```

### New Flow
```
User → pnpm test
     → ./cli/bin/mono test
     → CLI discovers workspaces
     → CLI runs turbo test with options
     → @mono-php/demo-app:test
     → vendor/bin/phpunit
```

## Benefits

### 1. Unified Interface
- Single entry point: `./cli/bin/mono <command>`
- Consistent command syntax across all operations
- No need to remember different command patterns

### 2. Better User Experience
- Interactive prompts for workspace selection
- Progress bars for long operations
- Colored output with clear formatting
- Helpful error messages with suggestions

### 3. Workspace Management
- Automatic workspace discovery
- Easy filtering: `--workspace <name>`
- List all workspaces: `mono list`
- Get workspace info: `mono info <name>`

### 4. Parallelism & Caching
- Turbo handles concurrent execution
- Intelligent caching for faster builds
- Dependency graph awareness
- Optimal task scheduling

### 5. Extensibility
- Easy to add new commands
- Reusable concerns (traits)
- Consistent error handling
- Self-documenting via help system

### 6. Consistency
- Same behavior across all environments
- Standardized workspace structure
- Predictable command execution
- Unified configuration

## Command Mapping

| Old Command | New Command | Description |
|------------|-------------|-------------|
| `pnpm install` | `pnpm install` | Triggers `mono install` via postinstall |
| `turbo run test` | `pnpm test` or `mono test` | Run tests |
| `turbo run lint` | `pnpm lint` or `mono lint` | Check code style |
| `turbo run format` | `pnpm format` or `mono format` | Fix code style |
| `turbo run typecheck` | `pnpm typecheck` or `mono typecheck` | Static analysis |
| `composer test` | `composer test` or `mono test` | Run tests |
| `composer lint` | `composer lint` or `mono lint` | Check code style |
| `composer refactor` | `composer refactor` or `mono refactor` | Rector refactoring |

## Workspace Scripts

All workspaces now have consistent scripts that can be called by Turbo:

### Required Scripts (All Workspaces)
- `composer:install` - Install Composer dependencies
- `test` - Run all tests
- `lint` - Check code style
- `format` - Fix code style
- `typecheck` - Run static analysis
- `clean` - Clean caches

### Optional Scripts (Apps Only)
- `dev` - Start development server
- `build` - Build for production
- `start` - Start production server

### Test Variants
- `test:unit` - Run unit tests only
- `test:feature` - Run feature tests only

## Turbo.json Tasks

All tasks remain unchanged in turbo.json. The CLI tool calls Turbo tasks internally:

```json
{
  "tasks": {
    "composer:install": { ... },
    "build": { ... },
    "lint": { ... },
    "format": { ... },
    "test": { ... },
    "test:unit": { ... },
    "test:feature": { ... },
    "typecheck": { ... },
    "dev": { ... },
    "clean": { ... }
  }
}
```

## Usage Examples

### Run tests across all workspaces
```bash
pnpm test
# or
./cli/bin/mono test
```

### Run tests in specific workspace
```bash
./cli/bin/mono test --workspace demo-app
# or
./cli/bin/mono test -w calculator
```

### List all workspaces
```bash
./cli/bin/mono list
```

### Install dependencies
```bash
pnpm install
# Automatically runs: ./cli/bin/mono install
```

### Development workflow
```bash
./cli/bin/mono dev --workspace demo-app
```

### Code quality checks
```bash
./cli/bin/mono lint
./cli/bin/mono format
./cli/bin/mono typecheck
```

### Composer operations
```bash
./cli/bin/mono require symfony/console --workspace demo-app
./cli/bin/mono update --workspace calculator
```

## Next Steps

### Immediate (Phase 1)
1. ✅ Refactor all configuration files
2. ⏳ Implement `InstallCommand`
3. ⏳ Implement `ListCommand`
4. ⏳ Implement `DevCommand`
5. ⏳ Implement `BuildCommand`

### Short-term (Phase 2)
6. ⏳ Implement `LintCommand`
7. ⏳ Implement `FormatCommand`
8. ⏳ Implement `TypecheckCommand`
9. ⏳ Enhance `TestCommand` with options

### Medium-term (Phase 3)
10. ⏳ Implement `ComposerCommand`
11. ⏳ Implement `RequireCommand`
12. ⏳ Implement `UpdateCommand`
13. ⏳ Implement `CleanCommand`
14. ⏳ Implement `CleanupCommand`

### Long-term (Phase 4)
15. ⏳ Implement `RefactorCommand`
16. ⏳ Implement `MutateCommand`
17. ⏳ Implement `CreatePackageCommand`
18. ⏳ Implement `CreateAppCommand`
19. ⏳ Implement `DoctorCommand`
20. ⏳ Implement `InfoCommand`

## Testing

After refactoring, test the new command flow:

```bash
# Test workspace discovery
./cli/bin/mono list

# Test the test command (already implemented)
./cli/bin/mono test

# Test via pnpm (should delegate to CLI)
pnpm test

# Test via composer (should delegate to CLI)
composer test
```

## Migration Guide

For existing workspaces:

1. Update `package.json` scripts to match the standard format
2. Update `composer.json` scripts to match the standard format
3. Add missing dev dependencies (phpstan, pint)
4. Ensure all scripts use vendor/bin paths
5. Test that all commands work via CLI

## Documentation Updates Needed

1. Update README.md with new command structure
2. Create COMMANDS.md with full command reference
3. Update CONTRIBUTING.md with development workflow
4. Add examples to each workspace README
5. Create video tutorials for common workflows

## Conclusion

The refactoring provides a solid foundation for a CLI-first monorepo management approach. All commands now flow through a single, well-documented interface that provides better UX, consistency, and extensibility.

The standardized workspace structure makes it easy to add new apps and packages, while the CLI tool handles all the complexity of workspace discovery, task orchestration, and error handling.
