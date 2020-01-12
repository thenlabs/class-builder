<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
abstract class Helpers
{
    public static function validNameForClassMember(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name);
    }
}
