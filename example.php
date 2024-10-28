<?php

require_once 'vendor/autoload.php';

use DuckType\assertDuckType;
use DuckType\Exceptions\DuckTypeException;

// Define your types and classes
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

class InvalidDocument
{
    public function render($extraParam): string
    {
        return "Invalid content";
    }
}

try {
    $doc = new PDFDocument();
    assertDuckType($doc, Renderable::class); // Should pass

    $invalidDoc = new InvalidDocument();
    assertDuckType($invalidDoc, Renderable::class); // Should throw DuckTypeException
} catch (DuckTypeException $e) {
    echo "Duck typing assertion failed:\n";
    foreach ($e->getErrors() as $error) {
        echo "- $error\n";
    }
}
