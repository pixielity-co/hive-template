# Turborepo Configuration Guide

This document explains the comprehensive `turbo.json` configuration for this PHP monorepo.

## Global Configuration

### UI & Daemon

```json
{
  "ui": "tui",
  "daemon": true
}
```

- **ui**: Terminal UI mode. `"tui"` provides an interactive interface for viewing logs
- **daemon**: Background process for performance optimization (auto-disabled in CI)

### Environment Mode

```json
{
  "envMode": "strict"
}
```

Strict mode filters environment variables to only those specified in `env`, `globalEnv`, and `passThroughEnv`. This ensures reproducible builds and prevents accidental environment leakage.

### Concurrency

```json
{
  "concurrency": "10"
}
```

Maximum number of tasks to run in parallel. Can be:
- Integer (e.g., `"10"`)
- Percentage (e.g., `"50%"`)
- `"1"` for serial execution

### Cache Directory

```json
{
  "cacheDir": ".turbo/cache"
}
```

Location for Turborepo's local cache. In Git worktrees, cache is automatically shared with the main worktree unless explicitly set.

### Global Dependencies

```json
{
  "globalDependencies": [
    ".env",
    ".env.example",
    "composer.json",
    "composer.lock",
    "pnpm-lock.yaml",
    "turbo.json",
    "tooling/**"
  ]
}
```

Files that affect ALL tasks. Changes to these files invalidate the entire cache. Includes:
- Environment files
- Lock files
- Turbo configuration
- Shared tooling configs

### Global Environment Variables

```json
{
  "globalEnv": [
    "NODE_ENV",
    "CI",
    "VERCEL",
    "GITHUB_ACTIONS"
  ]
}
```

Environment variables that affect the hash of ALL tasks. Changes cause all tasks to miss cache.

### Global Pass-Through Environment Variables

```json
{
  "globalPassThroughEnv": [
    "HOME",
    "PATH",
    "SHELL",
    "USER",
    "TMPDIR",
    "LANG",
    "LC_ALL",
    "TZ"
  ]
}
```

Environment variables made available to all tasks but DON'T affect cache keys. Includes system variables needed for proper execution.

## Remote Cache Configuration

```json
{
  "remoteCache": {
    "enabled": true,
    "signature": false,
    "preflight": false,
    "timeout": 30,
    "uploadTimeout": 60
  }
}
```

- **enabled**: Enable remote caching (requires `turbo login` and `turbo link`)
- **signature**: Verify artifact signatures (requires `TURBO_REMOTE_CACHE_SIGNATURE_KEY`)
- **preflight**: Send OPTIONS request before each HTTP request
- **timeout**: General timeout for remote cache operations (seconds)
- **uploadTimeout**: Specific timeout for uploads (seconds)

## Task Definitions

### composer:install

```json
{
  "composer:install": {
    "description": "Install PHP dependencies via Composer",
    "inputs": ["composer.json", "composer.lock"],
    "outputs": ["vendor/**", "composer.lock"],
    "cache": true,
    "passThroughEnv": [
      "COMPOSER_AUTH",
      "COMPOSER_HOME",
      "COMPOSER_CACHE_DIR"
    ],
    "outputLogs": "hash-only"
  }
}
```

**Purpose**: Install PHP dependencies in each workspace

**Caching Strategy**:
- Inputs: `composer.json` and `composer.lock`
- Outputs: `vendor/` directory and `composer.lock`
- Cache hit: Skip installation, restore from cache
- Cache miss: Run `composer install`, cache results

**Environment**: Composer-specific variables passed through but don't affect cache

### build

```json
{
  "build": {
    "description": "Build production assets",
    "dependsOn": ["^build", "composer:install"],
    "inputs": [
      "$TURBO_DEFAULT$",
      ".env",
      ".env.example",
      "!**/*.md",
      "!**/*.test.php",
      "!tests/**"
    ],
    "outputs": [
      "public/build/**",
      "public/hot",
      "bootstrap/cache/**"
    ],
    "cache": true,
    "env": ["APP_ENV", "APP_KEY", "APP_URL", "VITE_*"],
    "outputLogs": "new-only"
  }
}
```

**Purpose**: Build production assets (Vite, Laravel Mix, etc.)

**Dependencies**:
- `^build`: Wait for upstream packages to build first
- `composer:install`: Ensure dependencies are installed

**Inputs**:
- All default files (respecting `.gitignore`)
- Environment files
- Excludes: Markdown, test files, test directories

**Outputs**: Built assets in `public/build/`, hot reload file, cached configs

**Environment**: App configuration and Vite variables affect cache

### lint

```json
{
  "lint": {
    "description": "Check code style with Pint",
    "dependsOn": ["^lint", "composer:install"],
    "inputs": [
      "$TURBO_DEFAULT$",
      "!**/*.md",
      "!vendor/**",
      "!node_modules/**"
    ],
    "outputs": [],
    "cache": true,
    "outputLogs": "errors-only"
  }
}
```

**Purpose**: Check code style without modifying files

**Caching**: Caches the result (pass/fail) but no file outputs

**Logging**: Only show output when errors occur

### format

```json
{
  "format": {
    "description": "Fix code style with Pint",
    "dependsOn": ["composer:install"],
    "cache": false,
    "outputLogs": "new-only"
  }
}
```

**Purpose**: Fix code style (modifies files)

**Caching**: Disabled because it modifies source files

### test

```json
{
  "test": {
    "description": "Run PHPUnit tests",
    "dependsOn": ["^test", "composer:install"],
    "inputs": [
      "$TURBO_DEFAULT$",
      "phpunit.xml",
      "phpunit.xml.dist",
      "!vendor/**",
      "!node_modules/**"
    ],
    "outputs": [
      "tests/Datasets/**",
      "coverage/**",
      ".phpunit.cache/**"
    ],
    "cache": true,
    "env": ["APP_ENV", "DB_CONNECTION", "DB_DATABASE"],
    "outputLogs": "errors-only"
  }
}
```

**Purpose**: Run all PHPUnit tests

**Outputs**: Test datasets, coverage reports, PHPUnit cache

**Environment**: Test environment variables affect cache

### test:unit & test:feature

Specialized test tasks that run specific test suites:
- `test:unit`: Only unit tests (no database)
- `test:feature`: Only feature tests (with database)

### typecheck

```json
{
  "typecheck": {
    "description": "Run static analysis with PHPStan/Larastan",
    "dependsOn": ["^typecheck", "composer:install"],
    "inputs": [
      "$TURBO_DEFAULT$",
      "phpstan.neon",
      "phpstan.neon.dist",
      "$TURBO_ROOT$/tooling/phpstan/**",
      "!vendor/**",
      "!node_modules/**",
      "!**/*.md"
    ],
    "outputs": [".phpstan.cache/**"],
    "cache": true,
    "outputLogs": "errors-only"
  }
}
```

**Purpose**: Static analysis with PHPStan

**Inputs**: Includes shared PHPStan configs from `tooling/phpstan/`

**Outputs**: PHPStan cache for faster subsequent runs

### dev

```json
{
  "dev": {
    "description": "Start development server",
    "dependsOn": ["composer:install"],
    "cache": false,
    "persistent": true,
    "interactive": false,
    "env": [
      "APP_*",
      "DB_*",
      "REDIS_*",
      "MAIL_*",
      "AWS_*",
      "VITE_*"
    ],
    "outputLogs": "full"
  }
}
```

**Purpose**: Long-running development server

**Persistent**: Won't exit, other tasks can't depend on it

**Caching**: Disabled (long-running process)

**Environment**: All app-related variables available

### start

```json
{
  "start": {
    "description": "Start production server",
    "dependsOn": ["build"],
    "cache": false,
    "persistent": true,
    "env": ["APP_*", "DB_*", "REDIS_*", "MAIL_*", "AWS_*"]
  }
}
```

**Purpose**: Production server (waits for build to complete)

**Dependencies**: Ensures `build` completes before starting

### deploy

```json
{
  "deploy": {
    "description": "Full deployment pipeline",
    "dependsOn": ["build", "test", "lint", "typecheck"],
    "cache": true,
    "outputLogs": "errors-only"
  }
}
```

**Purpose**: Orchestrate full deployment pipeline

**Dependencies**: All quality checks must pass before deployment

## Special Syntax

### $TURBO_DEFAULT$

Includes Turborepo's default behavior (respects `.gitignore`):

```json
{
  "inputs": ["$TURBO_DEFAULT$", "!README.md"]
}
```

This includes all files except those in `.gitignore` and `README.md`.

### $TURBO_ROOT$

Reference files relative to repository root:

```json
{
  "inputs": ["$TURBO_ROOT$/tsconfig.json", "src/**/*.ts"]
}
```

### $TURBO_EXTENDS$

Append to inherited configuration instead of replacing:

```json
{
  "outputs": ["$TURBO_EXTENDS$", ".next/**"]
}
```

## Environment Variable Patterns

### Wildcards

```json
{
  "env": ["VITE_*", "MY_API_*"]
}
```

Matches all variables starting with the prefix.

### Negation

```json
{
  "env": ["!MY_SECRET_*"]
}
```

Excludes variables matching the pattern.

## Output Logging Modes

| Mode | Description |
|------|-------------|
| `full` | Display all logs |
| `hash-only` | Only show task hashes |
| `new-only` | Only show logs from cache misses |
| `errors-only` | Only show logs from failures |
| `none` | Hide all task logs |

## Cache Strategy

### What Gets Cached

1. **Task outputs** (files specified in `outputs`)
2. **Task logs** (always cached when caching is enabled)
3. **Task exit code** (success/failure)

### Cache Key Calculation

Cache key is a hash of:
- Task inputs (files matching `inputs` globs)
- Environment variables (specified in `env`)
- Global dependencies
- Global environment variables
- Task configuration
- Upstream task hashes (from `dependsOn`)

### Cache Hit Behavior

When cache hit occurs:
1. Restore files from `outputs`
2. Replay logs
3. Skip task execution
4. Return cached exit code

### Cache Miss Behavior

When cache miss occurs:
1. Execute task
2. Capture logs
3. Capture outputs
4. Store in cache
5. Return actual exit code

## Best Practices

### 1. Use Strict Environment Mode

Always use `"envMode": "strict"` to ensure reproducible builds and prevent environment leakage.

### 2. Minimize Global Dependencies

Only include files in `globalDependencies` that truly affect all tasks. Too many global dependencies cause unnecessary cache invalidation.

### 3. Specify Inputs Explicitly

For tasks with expensive operations, explicitly specify `inputs` to avoid unnecessary cache misses:

```json
{
  "typecheck": {
    "inputs": [
      "src/**/*.php",
      "phpstan.neon",
      "!**/*.md"
    ]
  }
}
```

### 4. Use Appropriate Output Logging

- Development: `"full"` or `"new-only"`
- CI: `"errors-only"` or `"hash-only"`
- Production: `"errors-only"`

### 5. Mark Long-Running Tasks as Persistent

```json
{
  "dev": {
    "persistent": true,
    "cache": false
  }
}
```

This prevents other tasks from depending on them and hanging.

### 6. Leverage Remote Caching

Enable remote caching for CI/CD and team collaboration:

```bash
turbo login
turbo link
```

### 7. Use Task Descriptions

Add descriptions to all tasks for better documentation:

```json
{
  "build": {
    "description": "Build production assets with Vite"
  }
}
```

## Troubleshooting

### Cache Not Working

1. Check if inputs are correctly specified
2. Verify environment variables are in `env` or `globalEnv`
3. Ensure outputs are being generated
4. Check `.turbo/cache/` directory exists

### Tasks Not Running in Parallel

1. Check `dependsOn` relationships
2. Verify `concurrency` setting
3. Ensure tasks aren't marked as `persistent`

### Environment Variables Not Available

1. Add to `passThroughEnv` or `globalPassThroughEnv`
2. Verify `envMode` is set correctly
3. Check if variables are in `env` (affects cache) vs `passThroughEnv` (doesn't affect cache)

### Remote Cache Not Working

1. Run `turbo login` and `turbo link`
2. Check `remoteCache.enabled` is `true`
3. Verify network connectivity
4. Check timeout settings

## CI/CD Integration

### GitHub Actions Example

```yaml
- name: Setup Turbo
  run: |
    turbo login --token=${{ secrets.TURBO_TOKEN }}
    turbo link

- name: Build
  run: pnpm turbo run build

- name: Test
  run: pnpm turbo run test
```

### Environment Variables in CI

Set these in your CI environment:
- `CI=true` (auto-detected by most CI providers)
- `TURBO_TOKEN` (for remote caching)
- `TURBO_TEAM` (team ID for remote cache)

## Performance Tips

1. **Use remote caching** to share cache across CI and developers
2. **Optimize inputs** to avoid unnecessary cache misses
3. **Use `hash-only` logging** in CI to reduce log noise
4. **Enable daemon** for faster task execution (auto-enabled)
5. **Set appropriate concurrency** based on available resources

## References

- [Turborepo Documentation](https://turbo.build/repo/docs)
- [Configuration Reference](https://turbo.build/repo/docs/reference/configuration)
- [Caching Guide](https://turbo.build/repo/docs/core-concepts/caching)
- [Remote Caching](https://turbo.build/repo/docs/core-concepts/remote-caching)
