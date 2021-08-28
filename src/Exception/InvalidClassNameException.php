<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidClassNameException extends InvalidMemberNameException
{
    public function __construct(string $name)
    {
        parent::__construct("The class name '{$name}' is invalid.");
    }
}
