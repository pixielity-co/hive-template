# CLI Commands Implementation Plan

## Overview

All commands will be routed through the CLI tool (`./cli/bin/mono`) to provide:
- Unified command interface
- Better error handling and user feedback
- Interactive prompts where needed
- Workspace filtering and selection
- Progress indicators for long operations
- Consistent output formatting

## Command Architecture

### Command Categories

1. **Lifecycle Commands** - Setup, installation, cleanup
2. **Development Commands** - Dev servers, build, watch
3. **Quality Commands** - Lint, format, typecheck, test
4. **Composer Commands** - Dependency management
5. **Turborepo Commands** - Task orchestration
6. **Workspace Commands** - Workspace management
7. **Utility Commands** - Info, list, help

## Detailed Command Specifications

### 1. Lifecycle Commands

#### `mono install`
**Description**: Install all dependencies (Composer + npm)
**Aliases**: `i`, `setup`
**Options**:
- `--workspace, -w <name>` - Install for specific workspace
- `--force, -f` - Force reinstall
- `--no-cache` - Disable Turbo cache
**Implementation**:
```php
#[AsCommand(name: 'install', description: 'Install all dependencies')]
class InstallCommand extends BaseCommand
{
    // 1. Show intro
    // 2. Discover workspaces
    // 3. Run turbo composer:install
    // 4. Show summary with timing
}
```

#### `mono clean`
**Description**: Clean caches and build artifacts
**Options**:
- `--workspace, -w <name>` - Clean specific workspace
- `--deep` - Also remove vendor and node_modules
**Implementation**:
```php
#[AsCommand(name: 'clean', description: 'Clean caches and artifacts')]
class CleanCommand extends BaseCommand
{
    // 1. Run turbo clean
    // 2. Optionally remove vendor/node_modules
    // 3. Clear .turbo cache
}
```

#### `mono cleanup`
**Description**: Deep clean - remove all generated files
**Warning**: Destructive operation
**Implementation**:
```php
#[AsCommand(name: 'cleanup', description: 'Deep clean all generated files')]
class CleanupCommand extends BaseCommand
{
    // 1. Confirm with user
    // 2. Remove lock files
    // 3. Remove vendor/node_modules
    // 4. Remove caches
}
```

### 2. Development Commands

#### `mono dev`
**Description**: Start development server
**Options**:
- `--workspace, -w <name>` - Start specific workspace
- `--port, -p <port>` - Custom port
**Implementation**:
```php
#[AsCommand(name: 'dev', description: 'Start development server')]
class DevCommand extends BaseCommand
{
    // 1. Select workspace if not specified
    // 2. Run turbo dev with filter
    // 3. Stream output
}
```

#### `mono build`
**Description**: Build for production
**Options**:
- `--workspace, -w <name>` - Build specific workspace
- `--force, -f` - Force rebuild
**Implementation**:
```php
#[AsCommand(name: 'build', description: 'Build for production')]
class BuildCommand extends BaseCommand
{
    // 1. Run turbo build
    // 2. Show progress
    // 3. Display build summary
}
```

### 3. Quality Commands

#### `mono lint`
**Description**: Check code style with Pint
**Options**:
- `--workspace, -w <name>` - Lint specific workspace
- `--fix` - Auto-fix issues
**Implementation**:
```php
#[AsCommand(name: 'lint', description: 'Check code style')]
class LintCommand extends BaseCommand
{
    // 1. Run turbo lint (or format if --fix)
    // 2. Parse output
    // 3. Show formatted results
}
```

#### `mono format`
**Description**: Fix code style with Pint
**Options**:
- `--workspace, -w <name>` - Format specific workspace
- `--check` - Check only, don't fix
**Implementation**:
```php
#[AsCommand(name: 'format', description: 'Fix code style')]
class FormatCommand extends BaseCommand
{
    // 1. Run turbo format
    // 2. Show files changed
}
```

#### `mono typecheck`
**Description**: Run static analysis with PHPStan
**Options**:
- `--workspace, -w <name>` - Check specific workspace
- `--level <0-9>` - PHPStan level
**Implementation**:
```php
#[AsCommand(name: 'typecheck', description: 'Run static analysis')]
class TypecheckCommand extends BaseCommand
{
    // 1. Run turbo typecheck
    // 2. Parse PHPStan output
    // 3. Show formatted errors
}
```

#### `mono test`
**Description**: Run PHPUnit tests
**Options**:
- `--workspace, -w <name>` - Test specific workspace
- `--unit` - Unit tests only
- `--feature` - Feature tests only
- `--coverage` - Generate coverage report
- `--filter <pattern>` - Filter tests
**Implementation**:
```php
#[AsCommand(name: 'test', description: 'Run tests')]
class TestCommand extends BaseCommand
{
    // 1. Determine test type
    // 2. Run turbo test/test:unit/test:feature
    // 3. Show test results
}
```

#### `mono refactor`
**Description**: Run Rector refactoring
**Options**:
- `--dry-run` - Show changes without applying
**Implementation**:
```php
#[AsCommand(name: 'refactor', description: 'Run Rector refactoring')]
class RefactorCommand extends BaseCommand
{
    // 1. Run composer refactor
    // 2. Show changes
    // 3. Confirm if not dry-run
}
```

#### `mono mutate`
**Description**: Run mutation testing with Infection
**Options**:
- `--workspace, -w <name>` - Test specific workspace
**Implementation**:
```php
#[AsCommand(name: 'mutate', description: 'Run mutation testing')]
class MutateCommand extends BaseCommand
{
    // 1. Run composer mutate
    // 2. Show mutation score
}
```

### 4. Composer Commands

#### `mono composer <command>`
**Description**: Run Composer command
**Options**:
- `--workspace, -w <name>` - Run in specific workspace
**Examples**:
- `mono composer require symfony/console`
- `mono composer update`
**Implementation**:
```php
#[AsCommand(name: 'composer', description: 'Run Composer command')]
class ComposerCommand extends BaseCommand
{
    // 1. Select workspace
    // 2. Run composer command
    // 3. Stream output
}
```

#### `mono require <package>`
**Description**: Require a Composer package
**Options**:
- `--workspace, -w <name>` - Add to specific workspace
- `--dev` - Add as dev dependency
**Implementation**:
```php
#[AsCommand(name: 'require', description: 'Require a package')]
class RequireCommand extends BaseCommand
{
    // 1. Select workspace
    // 2. Run composerRequire()
    // 3. Show success message
}
```

#### `mono update [package]`
**Description**: Update Composer dependencies
**Options**:
- `--workspace, -w <name>` - Update specific workspace
**Implementation**:
```php
#[AsCommand(name: 'update', description: 'Update dependencies')]
class UpdateCommand extends BaseCommand
{
    // 1. Select workspace
    // 2. Run composerUpdate()
    // 3. Show updated packages
}
```

### 5. Turborepo Commands

#### `mono turbo <command>`
**Description**: Run Turbo command directly
**Options**:
- `--filter <pattern>` - Filter workspaces
- `--force` - Force execution
- `--no-cache` - Disable cache
**Implementation**:
```php
#[AsCommand(name: 'turbo', description: 'Run Turbo command')]
class TurboCommand extends BaseCommand
{
    // 1. Build turbo options
    // 2. Run turbo command
    // 3. Stream output
}
```

#### `mono run <task>`
**Description**: Run a Turbo task
**Options**:
- `--workspace, -w <name>` - Run in specific workspace
- `--parallel` - Run in parallel
- `--continue` - Continue on error
**Implementation**:
```php
#[AsCommand(name: 'run', description: 'Run a Turbo task')]
class RunCommand extends BaseCommand
{
    // 1. Validate task exists
    // 2. Run turboRun()
    // 3. Show results
}
```

### 6. Workspace Commands

#### `mono list`
**Description**: List all workspaces
**Options**:
- `--apps` - Show apps only
- `--packages` - Show packages only
- `--json` - Output as JSON
**Implementation**:
```php
#[AsCommand(name: 'list', description: 'List workspaces')]
class ListCommand extends BaseCommand
{
    // 1. Get workspaces
    // 2. Format as table or JSON
    // 3. Show summary
}
```

#### `mono info [workspace]`
**Description**: Show workspace information
**Implementation**:
```php
#[AsCommand(name: 'info', description: 'Show workspace info')]
class InfoCommand extends BaseCommand
{
    // 1. Get workspace metadata
    // 2. Show composer.json info
    // 3. Show package.json info
    // 4. Show dependencies
}
```

#### `mono create:package <name>`
**Description**: Create a new package
**Options**:
- `--template <name>` - Use template
**Implementation**:
```php
#[AsCommand(name: 'create:package', description: 'Create a package')]
class CreatePackageCommand extends BaseCommand
{
    // 1. Prompt for details
    // 2. Create directory structure
    // 3. Generate composer.json
    // 4. Generate package.json
    // 5. Create README
}
```

#### `mono create:app <name>`
**Description**: Create a new app
**Options**:
- `--template <name>` - Use template
**Implementation**:
```php
#[AsCommand(name: 'create:app', description: 'Create an app')]
class CreateAppCommand extends BaseCommand
{
    // 1. Prompt for details
    // 2. Create directory structure
    // 3. Generate composer.json
    // 4. Generate package.json
    // 5. Create README
}
```

### 7. Utility Commands

#### `mono version`
**Description**: Show version information
**Implementation**:
```php
#[AsCommand(name: 'version', description: 'Show version')]
class VersionCommand extends BaseCommand
{
    // 1. Show CLI version
    // 2. Show Composer version
    // 3. Show Turbo version
    // 4. Show PHP version
}
```

#### `mono doctor`
**Description**: Check system requirements
**Implementation**:
```php
#[AsCommand(name: 'doctor', description: 'Check system')]
class DoctorCommand extends BaseCommand
{
    // 1. Check PHP version
    // 2. Check Composer
    // 3. Check Turbo
    // 4. Check extensions
    // 5. Show recommendations
}
```

## Refactored Configuration Files

### Root package.json
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

### Root composer.json
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

### Workspace package.json (apps/packages)
```json
{
  "scripts": {
    "composer:install": "composer install --no-interaction --prefer-dist --optimize-autoloader",
    "dev": "php -S localhost:8000 -t public",
    "build": "echo 'Build task'",
    "test": "vendor/bin/phpunit",
    "lint": "vendor/bin/pint --test",
    "format": "vendor/bin/pint",
    "typecheck": "vendor/bin/phpstan analyse",
    "clean": "rm -rf bootstrap/cache/* storage/framework/cache/*"
  }
}
```

### Workspace composer.json (apps/packages)
```json
{
  "scripts": {
    "test": "phpunit",
    "lint": "pint --test",
    "format": "pint",
    "typecheck": "phpstan analyse"
  }
}
```

## Implementation Priority

### Phase 1: Core Commands (Week 1)
1. ✅ `mono test` - Already working via TestCommand
2. `mono install` - Critical for setup
3. `mono list` - Workspace discovery
4. `mono dev` - Development workflow
5. `mono build` - Production builds

### Phase 2: Quality Commands (Week 2)
6. `mono lint` - Code style checking
7. `mono format` - Code style fixing
8. `mono typecheck` - Static analysis
9. `mono test` (enhanced) - Full test suite

### Phase 3: Composer Integration (Week 3)
10. `mono composer` - Direct Composer access
11. `mono require` - Add dependencies
12. `mono update` - Update dependencies

### Phase 4: Advanced Commands (Week 4)
13. `mono refactor` - Rector integration
14. `mono mutate` - Mutation testing
15. `mono create:package` - Scaffolding
16. `mono create:app` - Scaffolding
17. `mono doctor` - System check
18. `mono info` - Workspace info

## Benefits of CLI-First Approach

1. **Unified Interface**: Single entry point for all operations
2. **Better UX**: Interactive prompts, progress bars, colored output
3. **Error Handling**: Consistent error messages and recovery
4. **Workspace Filtering**: Easy workspace selection
5. **Parallelism**: Turbo handles concurrent execution
6. **Caching**: Turbo caching for faster operations
7. **Extensibility**: Easy to add new commands
8. **Documentation**: Self-documenting via help system
9. **Testing**: Easier to test CLI commands
10. **Consistency**: Same behavior across all environments

## Next Steps

1. Refactor all package.json and composer.json files
2. Implement Phase 1 commands
3. Update documentation
4. Add tests for commands
5. Create command templates for easy scaffolding
