<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ExistentClassException extends ClassBuilderException
{
    public function __construct(string $class)
    {
        parent::__construct("The class '{$class}' already exists.");
    }
}
