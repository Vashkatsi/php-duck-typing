<?php
/**
 * DuckType Library
 *
 * @license BSD-3-Clause
 * @link https://github.com/vashkatsi/ducktype
 */

namespace DuckType\Exceptions;

use Exception;

class DuckTypeException extends Exception
{
    protected array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Duck typing assertion failed.');
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function __toString(): string
    {
        return "Duck typing assertion failed:\n".implode("\n", $this->errors);
    }
}
