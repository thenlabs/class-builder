<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidConstantNameException extends ClassBuilderException
{
    public function __construct(string $name)
    {
        parent::__construct("The constant name '{$name}' is invalid.");
    }
}
