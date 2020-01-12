<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class UnexistentInterfaceException extends ClassBuilderException
{
    public function __construct(string $interface)
    {
        parent::__construct("The interface '{$interface}' not exists.");
    }
}
