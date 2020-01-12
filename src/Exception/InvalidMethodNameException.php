<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidMethodNameException extends ClassBuilderException
{
    public function __construct(string $name)
    {
        parent::__construct("The method name '{$name}' is invalid.");
    }
}
