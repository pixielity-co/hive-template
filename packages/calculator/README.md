# Calculator Package

A simple calculator package for testing the PHP monorepo structure.

## Installation

This package is part of the monorepo and is installed automatically.

## Usage

```php
use MonoPhp\Calculator\Calculator;

$calc = new Calculator();

echo $calc->add(5, 3);        // 8
echo $calc->subtract(10, 4);  // 6
echo $calc->multiply(3, 7);   // 21
echo $calc->divide(15, 3);    // 5
```

## Testing

```bash
composer test
```
