# Command Structure Plan

## Current State Analysis

### Root Level (Monorepo Orchestration)

**package.json (pnpm/npm scripts)**
- Entry point for developers
- Delegates to Turbo for parallel execution
- Handles Node.js ecosystem tasks

**composer.json (PHP scripts)**
- Handles PHP-specific tooling (Rector, Infection)
- Runs commands across workspaces sequentially
- No parallel execution capability

### Workspace Level (Apps & Packages)

**package.json (per workspace)**
- Simple, consistent interface
- Wraps PHP commands for Turbo compatibility
- No complex logic

**composer.json (per workspace)**
- PHP dependencies
- Laravel-specific hooks (apps only)
- Minimal scripts

## Problem Statement

1. **Redundancy**: Commands exist in both package.json and composer.json
2. **No unified CLI**: Developers need to know when to use `pnpm` vs `composer`
3. **Limited composer**: Composer scripts can't run in parallel
4. **Complexity**: Root composer.json has hardcoded workspace paths

## Proposed Solution: Symfony Console CLI

Create a unified CLI tool (`mono`) using Symfony Console that:
- Provides a single entry point for all commands
- Wraps both pnpm/turbo and composer
- Offers workspace discovery and filtering
- Provides better UX (colors, progress, help)

## Command Hierarchy

```
mono
├── install              # Install all dependencies
├── dev                  # Start dev servers
├── build                # Build all workspaces
├── test                 # Run tests
│   ├── --filter=api     # Filter to specific workspace
│   └── --unit           # Run unit tests only
├── lint                 # Check code style
├── format               # Fix code style
├── typecheck            # Run static analysis
├── refactor             # Run Rector
│   └── --dry            # Dry run
├── mutate               # Run mutation testing
├── clean                # Clear caches
├── cleanup              # Nuclear clean
├── deploy               # Full deployment pipeline
├── artisan              # Laravel artisan commands
│   └── migrate          # Example: mono artisan migrate
├── composer             # Composer commands
│   └── require          # Example: mono composer api require package
└── workspace
    ├── list             # List all workspaces
    └── info <name>      # Show workspace info
```

## Responsibility Matrix

### Root package.json (pnpm)
**Purpose**: Node.js ecosystem and Turbo orchestration

**Responsibilities**:
- Turbo task delegation
- Prettier formatting (non-PHP files)
- Cleanup operations
- Postinstall hooks

**Commands**:
```json
{
  "postinstall": "turbo run composer:install",
  "dev": "turbo run dev",
  "build": "turbo run build",
  "test": "turbo run test",
  "lint": "turbo run lint",
  "format": "turbo run format",
  "format:prettier": "prettier --write .",
  "typecheck": "turbo run typecheck",
  "clean": "turbo run clean --no-cache",
  "cleanup": "pnpm clean && rm -rf ...",
  "deploy": "turbo run deploy"
}
```

**What it DOESN'T do**:
- ❌ PHP-specific tooling (Rector, Infection)
- ❌ Workspace-specific operations
- ❌ Complex orchestration logic

### Root composer.json (PHP)
**Purpose**: PHP-specific tooling that can't run via Turbo

**Responsibilities**:
- Rector (needs full codebase view)
- Infection (needs full codebase view)
- Cross-workspace PHP operations

**Commands**:
```json
{
  "refactor": "vendor/bin/rector process",
  "refactor:dry": "vendor/bin/rector process --dry-run",
  "mutate": "vendor/bin/infection --threads=4"
}
```

**What it DOESN'T do**:
- ❌ Workspace-specific commands (use Turbo)
- ❌ Parallel execution (use Turbo)
- ❌ Node.js operations

### Workspace package.json (Apps & Packages)
**Purpose**: Turbo-compatible task interface

**Responsibilities**:
- Wrap PHP commands for Turbo
- Provide consistent interface
- Enable parallel execution

**Commands** (same for all workspaces):
```json
{
  "test": "vendor/bin/phpunit",
  "lint": "vendor/bin/pint --test",
  "format": "vendor/bin/pint",
  "typecheck": "vendor/bin/phpstan analyse",
  "clean": "rm -rf vendor .phpstan.cache",
  "composer:install": "composer install --no-interaction --prefer-dist --optimize-autoloader"
}
```

**Apps only**:
```json
{
  "dev": "php artisan serve",
  "build": "echo 'No build step'",
  "start": "php artisan serve",
  "setup": "composer run setup"
}
```

**What it DOESN'T do**:
- ❌ Complex logic
- ❌ Cross-workspace operations
- ❌ Orchestration

### Workspace composer.json (Apps & Packages)
**Purpose**: PHP dependencies and Laravel hooks

**Responsibilities**:
- Declare dependencies
- Laravel lifecycle hooks (apps only)
- Workspace-specific setup

**Apps**:
```json
{
  "scripts": {
    "setup": [
      "composer install",
      "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
      "@php artisan key:generate",
      "@php artisan migrate --force"
    ],
    "post-autoload-dump": ["..."],
    "post-update-cmd": ["..."]
  }
}
```

**Packages**:
```json
{
  "scripts": {}  // Usually empty
}
```

**What it DOESN'T do**:
- ❌ Task orchestration
- ❌ Cross-workspace operations

## Symfony Console CLI Structure

```
cli/
├── bin/
│   └── mono                    # Entry point
├── src/
│   ├── Command/
│   │   ├── InstallCommand.php
│   │   ├── DevCommand.php
│   │   ├── BuildCommand.php
│   │   ├── TestCommand.php
│   │   ├── LintCommand.php
│   │   ├── FormatCommand.php
│   │   ├── TypecheckCommand.php
│   │   ├── RefactorCommand.php
│   │   ├── MutateCommand.php
│   │   ├── CleanCommand.php
│   │   ├── CleanupCommand.php
│   │   ├── DeployCommand.php
│   │   ├── ArtisanCommand.php
│   │   ├── ComposerCommand.php
│   │   └── Workspace/
│   │       ├── ListCommand.php
│   │       └── InfoCommand.php
│   ├── Service/
│   │   ├── TurboService.php      # Wraps turbo CLI
│   │   ├── ComposerService.php   # Wraps composer CLI
│   │   ├── PnpmService.php       # Wraps pnpm CLI
│   │   └── WorkspaceDiscovery.php
│   └── Application.php
├── composer.json
└── README.md
```

## Command Implementation Strategy

### 1. Turbo-Delegated Commands
Commands that delegate to Turbo for parallel execution:

```php
class TestCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filter = $input->getOption('filter');
        $args = $filter ? "--filter=$filter" : '';
        
        return $this->turbo->run("test $args");
    }
}
```

**Commands**: dev, build, test, lint, format, typecheck, clean, deploy

### 2. Composer-Delegated Commands
Commands that delegate to root composer:

```php
class RefactorCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dry = $input->getOption('dry') ? ':dry' : '';
        
        return $this->composer->run("refactor$dry");
    }
}
```

**Commands**: refactor, mutate

### 3. Hybrid Commands
Commands that combine multiple operations:

```php
class InstallCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // 1. Install node deps
        $this->pnpm->run('install');
        
        // 2. Turbo runs composer:install in parallel
        // (handled by postinstall hook)
        
        return 0;
    }
}
```

**Commands**: install, cleanup

### 4. Workspace-Specific Commands
Commands that operate on specific workspaces:

```php
class ArtisanCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $workspace = $input->getArgument('workspace') ?? 'api';
        $command = $input->getArgument('command');
        
        $path = $this->workspaceDiscovery->getPath($workspace);
        
        return $this->process->run("cd $path && php artisan $command");
    }
}
```

**Commands**: artisan, composer

## Decision Matrix: When to Use What

| Scenario | Use | Reason |
|----------|-----|--------|
| Run tests in parallel | Turbo | Parallel execution, caching |
| Run Rector | Root composer | Needs full codebase view |
| Run Infection | Root composer | Needs full codebase view |
| Format code | Turbo | Parallel execution per workspace |
| Install deps | pnpm + Turbo | pnpm for node, Turbo for composer |
| Laravel artisan | CLI tool | Workspace-specific operation |
| Add composer package | CLI tool | Workspace-specific operation |
| Clean caches | Turbo | Parallel execution per workspace |
| Nuclear cleanup | pnpm script | Needs to remove pnpm itself |

## Migration Path

### Phase 1: Create CLI Tool
1. Create `cli/` directory structure
2. Add Symfony Console dependencies
3. Implement basic commands (install, dev, test)
4. Test with existing scripts

### Phase 2: Simplify Root Scripts
1. Remove redundant composer scripts
2. Keep only Rector and Infection in root composer
3. Update documentation

### Phase 3: Update Workflows
1. Update CI/CD to use CLI tool
2. Update developer documentation
3. Add CLI tool to PATH

### Phase 4: Deprecate Direct Usage
1. Add warnings to direct pnpm/composer usage
2. Encourage CLI tool usage
3. Eventually remove redundant scripts

## Benefits

1. **Single Entry Point**: Developers use `mono` for everything
2. **Better UX**: Colors, progress bars, help text
3. **Workspace Discovery**: Auto-detect workspaces
4. **Filtering**: Easy workspace filtering (`--filter=api`)
5. **Consistency**: Same interface for all commands
6. **Extensibility**: Easy to add new commands
7. **Type Safety**: PHP type hints for better IDE support
8. **Testing**: Unit test commands easily

## Open Questions

1. Should CLI tool be a separate package or in root?
2. Should we keep pnpm scripts for CI compatibility?
3. How to handle global installation (`composer global require`)?
4. Should CLI tool be published to Packagist?
5. How to handle version management?

## Next Steps

1. Create spec for Symfony Console CLI tool
2. Implement basic command structure
3. Test with existing workflows
4. Update documentation
5. Migrate CI/CD pipelines
