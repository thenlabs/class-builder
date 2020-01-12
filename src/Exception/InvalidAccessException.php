<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class InvalidAccessException extends ClassBuilderException
{
    public function __construct(string $access)
    {
        parent::__construct("The access '{$access}' is invalid. The accepted values they are 'public', 'private' or 'protected'.");
    }
}
