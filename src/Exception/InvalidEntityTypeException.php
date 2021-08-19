<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidEntityTypeException extends ClassBuilderException
{
    public function __construct(string $type)
    {
        parent::__construct("The value '{$type}' is an invalid entity type. The valid values are 'class', 'trait' or 'interface'.");
    }
}
