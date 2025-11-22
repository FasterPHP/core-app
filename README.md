# FasterPHP Core-App Architecture Guide

## Overview

The `core-app` library is a lightweight PHP framework component designed for fast application initialization and configuration management. It provides a foundation for PHP applications by handling environment setup, configuration loading, and application lifecycle management through a singleton pattern.

## Core Purpose

This library solves two fundamental problems in PHP application development:

1. **Application Initialization**: Provides a standardized way to bootstrap PHP applications with proper environment detection and configuration
2. **Configuration Management**: Offers a flexible, type-safe way to access hierarchical configuration data throughout your application

## Architecture Components

The codebase consists of three main classes that work together to provide a complete application initialization and configuration system:

### 1. App Class (`src/App.php`)

The App class is the central component that manages the application lifecycle and serves as the primary entry point for any application using this library.

**Design Pattern**: Singleton with controlled instantiation

**Key Responsibilities**:
- Environment detection and validation (development, testing, staging, production, build)
- Application root directory management
- Configuration instance management
- Global application state through singleton pattern

**Environment Management**:
The App class supports five distinct application environments, each serving a specific purpose in the development lifecycle:
- `development`: Local development with debugging enabled
- `testing`: Unit and integration testing environment
- `build`: CI/CD build processes
- `staging`: Pre-production testing environment
- `production`: Live production environment

The environment is determined through a priority chain that checks multiple sources in order:
1. Command-line arguments (highest priority, format: `-eAPPLICATION_ENV=value`)
2. PHP constants (defined via `define()`)
3. Environment variables (lowest priority, via `getenv()`)

If no environment is specified, the system defaults to `production` for safety, ensuring that applications don't accidentally run with development settings in production.

**Singleton Implementation**:
The App class uses a modified singleton pattern that allows normal instantiation but prevents multiple instances. This design choice provides flexibility for testing while maintaining the singleton guarantee in production code. The `setInstance()` method exists specifically for unit testing, allowing tests to reset the singleton state between test cases.

**Usage Pattern**:
```php
// Initialize the application (typically in your bootstrap/index.php)
$app = new App('/path/to/application/root');

// Access the singleton instance from anywhere in your application
$app = App::getInstance();

// Get environment information
$env = $app->getApplicationEnv(); // Returns 'production', 'development', etc.
$rootDir = $app->getRootDir(); // Returns the application root directory path

// Attach configuration
$config = new Config($configData);
$app->setConfig($config);

// Retrieve configuration
$config = $app->getConfig();
```

### 2. Config Class (`src/Config.php`)

The Config class provides an elegant interface for accessing hierarchical configuration data with both array-style and object-style access patterns.

**Design Pattern**: Recursive data wrapper with magic methods

**Key Features**:
- **Hierarchical Access**: Navigate nested configuration using dot notation or object property access
- **Type Safety**: Throws exceptions when accessing undefined configuration keys, preventing silent failures
- **Flexible Access Patterns**: Supports both `$config->get('key1', 'key2')` and `$config->key1->key2` syntax
- **Recursive Wrapping**: Automatically wraps array values in new Config instances for seamless nested access

**Implementation Details**:
The Config class uses PHP's magic `__get()` method to provide object-style property access. When you access a property that doesn't exist as a real class property, PHP calls `__get()`, which then calls the `get()` method internally. If the retrieved value is an array, it's automatically wrapped in a new Config instance, allowing for infinite nesting depth.

**Usage Pattern**:
```php
// Create configuration from array
$configData = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'credentials' => [
            'username' => 'admin',
            'password' => 'secret'
        ]
    ],
    'app' => [
        'name' => 'MyApp',
        'debug' => true
    ]
];

$config = new Config($configData);

// Access using object notation (recommended for readability)
$host = $config->database->host; // Returns 'localhost'
$username = $config->database->credentials->username; // Returns 'admin'

// Access using get() method with multiple keys
$port = $config->get('database', 'port'); // Returns 3306

// Convert back to array when needed
$dbConfig = $config->database->toArray(); // Returns the database array

// Accessing undefined keys throws an exception
try {
    $value = $config->nonexistent->key;
} catch (Exception $e) {
    // Exception: "Config not set for 'nonexistent'"
}
```

### 3. Exception Class (`src/Exception.php`)

A simple custom exception class that extends PHP's base Exception class, providing namespace-specific exception handling for the library.

**Purpose**:
- Allows applications to catch library-specific exceptions separately from other exceptions
- Provides clear error messaging for configuration and initialization issues
- Maintains exception type safety within the FasterPhp\CoreApp namespace

## Project Structure

```
core-app/
├── src/                          # Source code
│   ├── App.php                   # Application singleton and environment manager
│   ├── Config.php                # Configuration data wrapper
│   └── Exception.php             # Custom exception class
├── tests/                        # PHPUnit test suite
│   ├── AppTest.php              # Tests for App class
│   ├── ConfigTest.php           # Tests for Config class
│   ├── bootstrap.php            # Test suite initialization
│   └── configs/                 # Test configuration files
│       └── testing.php          # Sample test configuration
├── composer.json                # Dependency and autoload configuration
├── phpunit.xml                  # PHPUnit configuration
└── README.md                    # Project documentation
```

## Dependencies and Requirements

**PHP Version**: Requires PHP 8.2 or higher, leveraging modern PHP features including:
- Strict typing (`declare(strict_types=1)`)
- Type declarations for properties and return values
- Null safety with nullable types (`?Type`)

**Development Dependencies**:
- PHPUnit 9.5 for unit testing
- Optional: runkit7 extension for advanced testing scenarios (constant manipulation)

**Autoloading**:
The library uses PSR-4 autoloading with the namespace `FasterPhp\CoreApp` mapped to the `src/` directory. This follows PHP-FIG standards and integrates seamlessly with Composer-based projects.

## Testing Strategy

The test suite demonstrates comprehensive coverage of the library's functionality:

**Test Bootstrap** (`tests/bootstrap.php`):
- Loads Composer autoloader
- Sets error reporting to E_ALL for strict testing
- Configures timezone (Europe/London)
- Initializes a test App instance

**App Testing** (`tests/AppTest.php`):
Tests cover critical scenarios including:
- Singleton pattern enforcement (preventing double instantiation)
- Environment detection from various sources
- Default environment fallback to production
- Root directory validation
- Config attachment and retrieval
- Exception handling for invalid states

The tests use PHPUnit's setup/teardown hooks to manage the singleton state between tests, ensuring test isolation. Some tests require the runkit7 extension to manipulate PHP constants during testing, but these are gracefully skipped if the extension isn't available.

**Config Testing** (`tests/ConfigTest.php`):
Tests validate:
- Hierarchical data access patterns
- Exception throwing for undefined keys
- Recursive Config wrapping for nested arrays
- Object and primitive value handling
- Array conversion via `toArray()`

## Integration Patterns

### Typical Application Bootstrap

Here's how you would typically integrate this library into a PHP application:

```php
<?php
// public/index.php or bootstrap.php

require_once __DIR__ . '/../vendor/autoload.php';

use FasterPhp\CoreApp\App;
use FasterPhp\CoreApp\Config;

// Initialize the application
$rootDir = dirname(__DIR__);
$app = new App($rootDir);

// Load environment-specific configuration
$env = $app->getApplicationEnv();
$configFile = $rootDir . "/config/{$env}.php";

if (file_exists($configFile)) {
    $configData = require $configFile;
    $config = new Config($configData);
    $app->setConfig($config);
}

// Now your application can access the App singleton from anywhere
// Example in a controller or service:
$app = App::getInstance();
$dbHost = $app->getConfig()->database->host;
```

### Configuration File Structure

Configuration files should return associative arrays that can be deeply nested:

```php
<?php
// config/production.php
return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => 3306,
        'name' => 'production_db',
        'credentials' => [
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ]
    ],
    'cache' => [
        'driver' => 'redis',
        'ttl' => 3600
    ],
    'logging' => [
        'level' => 'error',
        'path' => '/var/log/app.log'
    ]
];
```

## Design Principles and Patterns

### 1. Singleton Pattern with Flexibility
The App class implements a singleton pattern but allows normal instantiation. This provides the benefits of global state management while maintaining testability. The `setInstance()` method enables test isolation by allowing the singleton to be reset between tests.

### 2. Fail-Fast Philosophy
Both App and Config classes throw exceptions immediately when encountering invalid states or missing data. This prevents silent failures and makes debugging easier by catching issues at the point of occurrence rather than allowing them to propagate.

### 3. Immutable Configuration
Once a Config object is created, its data cannot be modified. This immutability ensures that configuration remains consistent throughout the application lifecycle and prevents accidental modifications that could lead to bugs.

### 4. Environment-Aware Initialization
The App class automatically detects and validates the application environment, ensuring that applications always know their execution context. The priority chain (CLI args > constants > env vars) provides flexibility while maintaining predictability.

### 5. Type Safety
The library leverages PHP 8.2's type system extensively, using strict typing, type declarations, and nullable types to catch type-related errors at runtime and provide better IDE support.

## Key Architectural Decisions

### Why Singleton for App?
The singleton pattern ensures that environment detection and initialization happen exactly once per request. This is critical because the APPLICATION_ENV constant can only be defined once in PHP, and having multiple App instances would create inconsistent state.

### Why Magic Methods in Config?
The `__get()` magic method provides a clean, intuitive API for accessing nested configuration. The alternative would be verbose method chaining or array access, which is less readable. The magic method approach allows `$config->database->host` instead of `$config->get('database')->get('host')`.

### Why No Setters in Config?
Configuration is intentionally immutable after creation. This prevents accidental modifications and ensures that all parts of the application see the same configuration values. If configuration needs to change, a new Config instance should be created.

### Why Command-Line Args Have Highest Priority?
This allows developers and CI/CD systems to override environment settings without modifying code or environment variables. It's particularly useful for testing different environments locally or in automated pipelines.

## Common Use Cases

### 1. Multi-Environment Application
```php
// The App automatically detects the environment
$app = new App(__DIR__);
$env = $app->getApplicationEnv();

// Load environment-specific config
$config = new Config(require "config/{$env}.php");
$app->setConfig($config);

// Use environment-specific settings
if ($env === App::APPLICATIONENV_DEVELOPMENT) {
    ini_set('display_errors', '1');
}
```

### 2. Database Connection Management
```php
$app = App::getInstance();
$dbConfig = $app->getConfig()->database;

$pdo = new PDO(
    $dbConfig->dsn,
    $dbConfig->credentials->username,
    $dbConfig->credentials->password,
    $dbConfig->options->toArray()
);
```

### 3. Feature Flags
```php
$config = new Config([
    'features' => [
        'new_ui' => true,
        'beta_api' => false,
        'experimental' => [
            'ai_suggestions' => true
        ]
    ]
]);

if ($config->features->new_ui) {
    // Load new UI components
}
```

## Extension Points

While the library is intentionally minimal, it can be extended in several ways:

### 1. Custom App Subclass
```php
class MyApp extends App {
    protected function _initialise(): void {
        parent::_initialise();
        // Add custom initialization logic
        $this->loadPlugins();
        $this->setupErrorHandlers();
    }
}
```

### 2. Config Validation
```php
class ValidatedConfig extends Config {
    public function __construct(array $data) {
        $this->validate($data);
        parent::__construct($data);
    }
    
    private function validate(array $data): void {
        // Add validation logic
    }
}
```

## Summary

The FasterPHP core-app library provides a solid foundation for PHP application initialization and configuration management. Its architecture emphasizes simplicity, type safety, and fail-fast error handling. The singleton App class manages application lifecycle and environment detection, while the Config class provides elegant access to hierarchical configuration data. Together, these components enable rapid application bootstrapping with minimal boilerplate code while maintaining flexibility and testability.

The library's minimal dependencies, modern PHP 8.2 features, and comprehensive test coverage make it suitable for both small projects and large-scale applications that need a lightweight, reliable foundation for application initialization and configuration management.
