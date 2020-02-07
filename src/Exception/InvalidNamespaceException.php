<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidNamespaceException extends ClassBuilderException
{
    public function __construct(string $namespace)
    {
        parent::__construct("The namespace '{$namespace}' is invalid.");
    }
}
