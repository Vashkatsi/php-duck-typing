# DuckType

A PHP library for asserting duck typing and structural type compliance.

## Introduction

**DuckType** allows you to check whether an object conforms to a specific structure (methods and properties) defined by
an interface or class, without requiring the object to explicitly implement that interface or extend that class. This is
useful in scenarios where you want to ensure that an object can be used in a particular context based on its available
methods and properties, embracing the concept of duck typing:

> "If it looks like a duck and quacks like a duck, it's a duck."

## Installation

Install the package via Composer:

```bash
composer require vashkatsi/ducktype
```

## Usage

### Basic Example

```php
<?php
require 'vendor/autoload.php';

use function DuckType\assertDuckType;
use DuckType\Exceptions\DuckTypeException;

interface Renderable
{
    public function render(): string;
}

class PDFDocument
{
    public function render(): string
    {
        return "PDF content";
    }
}

$doc = new PDFDocument();

try {
    assertDuckType($doc, Renderable::class);
    echo "The object conforms to Renderable.";
} catch (DuckTypeException $e) {
    echo "Duck typing assertion failed:\n";
    foreach ($e->getErrors() as $error) {
        echo "- $error\n";
    }
}
```

### Handling Exceptions

```php
try {
    // Assume $invalidDoc is an object that doesn't conform
    assertDuckType($invalidDoc, Renderable::class);
} catch (DuckTypeException $e) {
    echo "Duck typing assertion failed:\n";
    foreach ($e->getErrors() as $error) {
        echo "- $error\n";
    }
}
```

### Checking Against Classes

```php
class Template
{
    public string $content;

    public function render(): string
    {
        return $this->content;
    }
}

class Page
{
    public string $content;

    public function render(): string
    {
        return $this->content;
    }
}

$page = new Page();

assertDuckType($page, Template::class); // Returns true
```

### Advanced Example with Union Types

```php
interface Processor
{
    public function process(int|string $data): bool;
}

class DataProcessor
{
    public function process(int|string $data): bool
    {
        // Processing logic
        return true;
    }
}

$processor = new DataProcessor();

assertDuckType($processor, Processor::class); // Returns true
```

## Running Tests

To run the test suite, first install development dependencies:

```bash
composer install
```

Then run PHPUnit:

```bash
composer test
```

Or directly:

```bash
vendor/bin/phpunit tests
```

To generate code coverage reports:

```bash
vendor/bin/phpunit --coverage-html coverage
```

Reports will be available in the `coverage` directory.

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on GitHub.

## License

This project is licensed under the BSD-3-Clause License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Inspired by the concept of duck typing in dynamic languages like Python and Go.
- Thanks to all contributors and users who have helped improve this library.

## Contact

- **Author**: Archil Abuladze
- **Email**: armiworker@gmail.com
- **GitHub**: [vashkatsi](https://github.com/vashkatsi)

Feel free to reach out if you have any questions or need assistance using the library.