<?php
/**
 * DuckType Library
 *
 * @license BSD-3-Clause
 * @link https://github.com/vashkatsi/ducktype
 */

namespace DuckType\Exceptions;

use LogicException;

class DuckTypeException extends LogicException
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
}
