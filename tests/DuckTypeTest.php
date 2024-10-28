<?php

namespace DuckType\Tests;

use PHPUnit\Framework\TestCase;
use DuckType\Exceptions\DuckTypeException;

use function DuckType\assertDuckType;

// Import the function

// Define test interfaces and classes outside of test methods

interface Renderable
{
    public function render(): string;
}

interface Processor
{
    public function process(string $data): bool;
}

interface BaseInterface
{
    public function execute(): void;
}

interface ExtendedInterface extends BaseInterface
{
    public function render(): string;
}

interface UnionInterface
{
    public function process(int|string $data): bool;
}

class PDFDocument
{
    public function render(): string
    {
        return "PDF content";
    }
}

class ImageDocument
{
    // Missing render method
}

class InvalidDocument
{
    public function render(): int
    {
        return 123;
    }
}

class DataProcessor
{
    public function process($data): bool
    {
        return true;
    }
}

class Command
{
    public function execute(): void
    {
        // Implementation
    }

    public function render(): string
    {
        return "Command output";
    }
}

class Template
{
    public string $content;
}

class Page
{
    public string $content;
}

class InvalidPage
{
    public int $content;
}

class UnionClass
{
    public function process(int|string $data): bool
    {
        return true;
    }
}

class InvalidUnionClass
{
    public function process(int $data): bool
    {
        return true;
    }
}

class DuckTypeTest extends TestCase
{
    public function testValidDuckType()
    {
        $doc = new PDFDocument();
        $this->assertTrue(assertDuckType($doc, Renderable::class));
    }

    public function testInvalidDuckTypeMissingMethod()
    {
        $doc = new ImageDocument();

        $this->expectException(DuckTypeException::class);
        $this->expectExceptionMessage("Method render not found in instance.");

        assertDuckType($doc, Renderable::class);
    }

    public function testInvalidDuckTypeWrongReturnType()
    {
        $doc = new InvalidDocument();

        $this->expectException(DuckTypeException::class);
        $this->expectExceptionMessage("Return type of method render does not match.");

        assertDuckType($doc, Renderable::class);
    }

    public function testInvalidDuckTypeParameterMismatch()
    {
        $processor = new DataProcessor();

        $this->expectException(DuckTypeException::class);
        $this->expectExceptionMessage("Parameter data in method process is missing type declaration.");

        assertDuckType($processor, Processor::class);
    }

    public function testValidDuckTypeWithInheritance()
    {
        $command = new Command();

        $this->assertTrue(assertDuckType($command, ExtendedInterface::class));
    }

    public function testDuckTypeWithProperties()
    {
        $page = new Page();
        $this->assertTrue(assertDuckType($page, Template::class));
    }

    public function testInvalidDuckTypePropertyMismatch()
    {
        $page = new InvalidPage();

        $this->expectException(DuckTypeException::class);
        $this->expectExceptionMessage("Property content has a type mismatch.");

        assertDuckType($page, Template::class);
    }

    public function testDuckTypeWithUnionTypes()
    {
        $instance = new UnionClass();
        $this->assertTrue(assertDuckType($instance, UnionInterface::class));
    }

    public function testInvalidDuckTypeWithUnionTypes()
    {
        $instance = new InvalidUnionClass();

        $this->expectException(DuckTypeException::class);
        $this->expectExceptionMessage("Parameter data in method process has type mismatch.");

        assertDuckType($instance, UnionInterface::class);
    }
}
