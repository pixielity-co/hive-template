# Commands Structure

## Overview

All CLI commands are now organized into logical folders for better maintainability and discoverability.

## Directory Structure

```
cli/src/Commands/
├── BaseCommand.php              # Base class for all commands
│
├── Composer/                    # Composer integration commands
│   ├── ComposerCommand.php      # Direct Composer access
│   ├── RequireCommand.php       # Add dependencies
│   └── UpdateCommand.php        # Update dependencies
│
├── Deploy/                      # Deployment commands
│   ├── DeployCommand.php        # Full deployment pipeline
│   └── PublishCommand.php       # Package publishing
│
├── Dev/                         # Development commands
│   ├── DevCommand.php           # Development server
│   └── BuildCommand.php         # Production builds
│
├── Lifecycle/                   # Lifecycle management commands
│   ├── InstallCommand.php       # Dependency installation
│   ├── CleanCommand.php         # Cache cleaning
│   └── CleanupCommand.php       # Deep cleaning
│
├── Make/                        # Scaffolding commands
│   ├── CreateAppCommand.php     # Create new app
│   └── CreatePackageCommand.php # Create new package
│
├── Quality/                     # Code quality commands
│   ├── TestCommand.php          # Run tests
│   ├── LintCommand.php          # Code style checking
│   ├── FormatCommand.php        # Code style fixing
│   ├── TypecheckCommand.php     # Static analysis
│   ├── RefactorCommand.php      # Rector refactoring
│   └── MutateCommand.php        # Mutation testing
│
├── Turbo/                       # Turborepo commands
│   ├── TurboCommand.php         # Direct Turbo access
│   └── RunCommand.php           # Run arbitrary tasks
│
├── Workspace/                   # Workspace management
│   ├── ListCommand.php          # List workspaces
│   └── InfoCommand.php          # Workspace information
│
└── Utility/                     # Utility commands
    ├── DoctorCommand.php        # System health check
    └── VersionCommand.php       # Version information
```

## Command Categories

### Composer Commands (`Composer/`)
Commands for managing Composer dependencies.

| Command | File | Description |
|---------|------|-------------|
| `composer` | ComposerCommand.php | Direct Composer access |
| `require` | RequireCommand.php | Add package dependency |
| `update` | UpdateCommand.php | Update dependencies |

### Deploy Commands (`Deploy/`)
Commands for deployment and publishing.

| Command | File | Description |
|---------|------|-------------|
| `deploy` | DeployCommand.php | Run deployment pipeline |
| `publish` | PublishCommand.php | Publish packages |

### Dev Commands (`Dev/`)
Commands for development workflow.

| Command | File | Description |
|---------|------|-------------|
| `dev` | DevCommand.php | Start development server |
| `build` | BuildCommand.php | Build for production |

### Lifecycle Commands (`Lifecycle/`)
Commands for installation and cleanup.

| Command | File | Description |
|---------|------|-------------|
| `install` | InstallCommand.php | Install all dependencies |
| `clean` | CleanCommand.php | Clean caches |
| `cleanup` | CleanupCommand.php | Deep clean |

### Make Commands (`Make/`)
Commands for scaffolding new workspaces.

| Command | File | Description |
|---------|------|-------------|
| `create:app` | CreateAppCommand.php | Scaffold new application |
| `create:package` | CreatePackageCommand.php | Scaffold new package |
| `make:workspace` | MakeWorkspaceCommand.php | Initialize new monorepo workspace |

### Quality Commands (`Quality/`)
Commands for code quality and testing.

| Command | File | Description |
|---------|------|-------------|
| `test` | TestCommand.php | Run PHPUnit tests |
| `lint` | LintCommand.php | Check code style |
| `format` | FormatCommand.php | Fix code style |
| `typecheck` | TypecheckCommand.php | Run static analysis |
| `refactor` | RefactorCommand.php | Run Rector refactoring |
| `mutate` | MutateCommand.php | Run mutation testing |

### Turbo Commands (`Turbo/`)
Commands for direct Turborepo access.

| Command | File | Description |
|---------|------|-------------|
| `turbo` | TurboCommand.php | Direct Turbo command access |
| `run` | RunCommand.php | Run arbitrary Turbo task |

### Workspace Commands (`Workspace/`)
Commands for workspace management and information.

| Command | File | Description |
|---------|------|-------------|
| `list` | ListCommand.php | List all workspaces |
| `info` | InfoCommand.php | Show workspace details |

### Utility Commands (`Utility/`)
Utility and diagnostic commands.

| Command | File | Description |
|---------|------|-------------|
| `doctor` | DoctorCommand.php | System health check |
| `version` | VersionCommand.php | Show version information |

## Namespace Structure

Commands follow PSR-4 autoloading with namespaces matching directory structure:

```php
// Base command
namespace MonoPhp\Cli\Commands;

// Composer commands
namespace MonoPhp\Cli\Commands\Composer;

// Deploy commands
namespace MonoPhp\Cli\Commands\Deploy;

// Dev commands
namespace MonoPhp\Cli\Commands\Dev;

// Lifecycle commands
namespace MonoPhp\Cli\Commands\Lifecycle;

// Make commands
namespace MonoPhp\Cli\Commands\Make;

// Quality commands
namespace MonoPhp\Cli\Commands\Quality;

// Turbo commands
namespace MonoPhp\Cli\Commands\Turbo;

// Workspace commands
namespace MonoPhp\Cli\Commands\Workspace;

// Utility commands
namespace MonoPhp\Cli\Commands\Utility;
```

## Command Discovery

All commands are automatically discovered by the `HasDiscovery` trait, which:
1. Recursively scans the `Commands/` directory
2. Identifies classes with `#[AsCommand]` attribute
3. Registers them with the Symfony Console application

No manual registration required!

## Documentation Standards

All commands follow consistent documentation patterns:

### Class-Level Docblock
```php
/**
 * Command Name.
 *
 * Brief description of what the command does.
 *
 * Detailed explanation of:
 * - Purpose and use cases
 * - Features and capabilities
 * - Workflow and process
 * - Integration with other tools
 *
 * Example usage:
 * ```bash
 * # Example 1
 * ./cli/bin/mono command-name
 *
 * # Example 2
 * ./cli/bin/mono command-name --option
 * ```
 *
 * @see RelatedCommand For related functionality
 * @see Trait For inherited functionality
 */
```

### Method-Level Docblock
```php
/**
 * Execute the command.
 *
 * Detailed explanation of:
 * - What the method does
 * - Step-by-step process
 * - Error handling
 * - Return values
 *
 * @param InputInterface  $input  Command input
 * @param OutputInterface $output Command output
 * @return int Exit code (0 for success, 1 for failure)
 */
```

### Inline Comments
- Explain complex logic
- Document important decisions
- Clarify non-obvious code
- Provide context for future maintainers

## Adding New Commands

### 1. Choose the Right Directory

- **Composer related?** → `Composer/`
- **Deployment/publishing?** → `Deploy/`
- **Development workflow?** → `Dev/`
- **Installation/cleanup?** → `Lifecycle/`
- **Scaffolding?** → `Make/`
- **Code quality/testing?** → `Quality/`
- **Turbo related?** → `Turbo/`
- **Workspace management?** → `Workspace/`
- **Utility/diagnostic?** → `Utility/`

### 2. Create Command File

```php
<?php

declare(strict_types=1);

namespace MonoPhp\Cli\Commands\Category;

use MonoPhp\Cli\Commands\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Your Command.
 *
 * Comprehensive description following the pattern.
 */
#[AsCommand(
    name: 'your-command',
    description: 'Brief description',
    aliases: ['alias1', 'alias2']
)]
final class YourCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->intro('Starting...');
        
        // Your logic here
        
        $this->outro('✓ Complete!');
        
        return Command::SUCCESS;
    }
}
```

### 3. Command Auto-Discovery

The command will be automatically discovered and registered. No manual registration needed!

### 4. Test the Command

```bash
# List all commands (verify yours appears)
./cli/bin/mono list

# Get help for your command
./cli/bin/mono help your-command

# Run your command
./cli/bin/mono your-command
```

## Benefits of This Structure

1. **Organization**: Related commands grouped together
2. **Discoverability**: Easy to find commands by category
3. **Maintainability**: Clear separation of concerns
4. **Scalability**: Easy to add new command categories
5. **Consistency**: All commands follow same patterns
6. **Auto-discovery**: No manual registration required
7. **IDE Support**: Better autocomplete and navigation

## Migration Notes

All existing commands have been moved to their appropriate directories with:
- ✅ Updated namespaces
- ✅ Comprehensive docblocks
- ✅ Detailed inline comments
- ✅ Consistent formatting
- ✅ Auto-discovery compatibility

No breaking changes - all commands work exactly as before!
