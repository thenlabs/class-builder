<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidPropertyTypeException extends ClassBuilderException
{
    public function __construct(string $type, string $property)
    {
        parent::__construct("Invalid type '{$type}' for property '{$property}'.");
    }
}
