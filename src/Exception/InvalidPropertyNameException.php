<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidPropertyNameException extends ClassBuilderException
{
    public function __construct(string $name)
    {
        parent::__construct("The property name '{$name}' is invalid.");
    }
}
