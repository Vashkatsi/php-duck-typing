<?php

namespace DuckType\Tests;

use PHPUnit\Framework\TestCase;
use DuckType\Exceptions\DuckTypeException;

use function DuckType\assertDuckType;

interface BaseRenderable
{
    public function render(): string;
}

interface AdvancedRenderable extends BaseRenderable
{
    public function advancedRender(): string;
}

interface MultiProcessor
{
    public function process(int $data): bool;
}

interface AdvancedProcessor extends MultiProcessor
{
    public function processData(int|string $data): bool;
}

class SimpleRenderableClass
{
    public function render(): string
    {
        return "Rendering content";
    }
}

class AdvancedRenderableClass extends SimpleRenderableClass implements AdvancedRenderable
{
    public function advancedRender(): string
    {
        return "Advanced rendering content";
    }
}

class MultiProcessorClass
{
    public function process(int $data): bool
    {
        return true;
    }
}

class FullProcessorClass extends MultiProcessorClass implements AdvancedProcessor
{
    public function processData(int|string $data): bool
    {
        return true;
    }
}

class InheritanceTest extends TestCase
{
    public function testValidInheritedInterfaceImplementation()
    {
        $instance = new AdvancedRenderableClass();
        $this->assertTrue(assertDuckType($instance, AdvancedRenderable::class));
    }

    public function testInvalidInheritedInterfaceMissingMethod()
    {
        $instance = new SimpleRenderableClass();

        $this->expectException(DuckTypeException::class);

        assertDuckType($instance, AdvancedRenderable::class);
    }

    public function testValidClassInheritanceWithInterface()
    {
        $instance = new FullProcessorClass();
        $this->assertTrue(assertDuckType($instance, AdvancedProcessor::class));
    }

    public function testInvalidClassInheritanceWithUnionTypes()
    {
        $instance = new class implements MultiProcessor {
            public function process(int $data): bool
            {
                return true;
            }

            public function processData(int $data): bool
            {
                return true;
            }
        };

        $this->expectException(DuckTypeException::class);

        assertDuckType($instance, AdvancedProcessor::class);
    }
}
