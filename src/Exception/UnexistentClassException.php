<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class UnexistentClassException extends ClassBuilderException
{
    public function __construct(string $class)
    {
        parent::__construct("The class '{$class}' not exists.");
    }
}
