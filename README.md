# 🐝 Hive Template

**PHP Monorepo Workspace Template powered by Turborepo**

This is the official template repository for creating new PhpHive workspaces. It provides a pre-configured monorepo structure with sample applications and packages.

## 🚀 Quick Start

### Using PhpHive CLI (Recommended)

```bash
# Install PhpHive CLI globally
composer global require phphive/cli

# Create a new workspace from this template
hive make:workspace my-project
```

### Using GitHub Template

1. Click "Use this template" button on GitHub
2. Create your new repository
3. Clone and start developing

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git
cd YOUR_REPO
pnpm install
composer install
```

## 📁 Project Structure

```
hive-template/
├── apps/                    # Applications
│   └── sample-app/         # Sample skeleton app
├── packages/                # Shared packages
│   └── sample-package/     # Sample package
├── tooling/                 # Shared tooling configs
├── composer.json            # Root composer file
├── package.json             # Root package file
├── pnpm-workspace.yaml      # PNPM workspace config
└── turbo.json              # Turborepo configuration
```

## 🎯 What's Included

### Sample App
- **Location**: `apps/sample-app/`
- **Type**: Skeleton PHP application
- **Features**: PHPUnit, basic structure, ready to extend

### Sample Package
- **Location**: `packages/sample-package/`
- **Type**: Shared PHP library
- **Features**: PSR-4 autoloading, PHPUnit tests

### Configuration
- **Turborepo**: Configured for parallel task execution and caching
- **PNPM Workspaces**: Efficient package management
- **Composer**: Monorepo-ready PHP dependency management

## 🛠️ Available Commands

```bash
# Install dependencies
pnpm install
composer install

# Development
pnpm dev              # Start development servers
pnpm build            # Build all packages

# Testing
pnpm test             # Run all tests
composer test         # Run PHP tests

# Code Quality
pnpm lint             # Lint all packages
composer lint         # Lint PHP code
```

## 📦 Using PhpHive CLI

Once you have a workspace, use PhpHive CLI to manage it:

```bash
# Create new app
hive make:app my-api --type=laravel

# Create new package
hive make:package my-library

# Run quality checks
hive quality:test
hive quality:lint
hive quality:typecheck

# Framework commands
hive framework:artisan migrate
hive framework:console cache:clear
```

## 🔗 Links

- **PhpHive CLI**: [github.com/pixielity-co/phphive-cli](https://github.com/pixielity-co/phphive-cli)
- **Documentation**: [pixielity.gitbook.io/phphive-cli](https://pixielity.gitbook.io/phphive-cli/)
- **Packagist**: [packagist.org/packages/phphive/cli](https://packagist.org/packages/phphive/cli)

## 📄 License

MIT License - feel free to use this template for your projects!

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

Made with ❤️ by the PhpHive team
