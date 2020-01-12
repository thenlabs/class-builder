<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class UnexistentTraitException extends ClassBuilderException
{
    public function __construct(string $trait)
    {
        parent::__construct("The trait '{$trait}' not exists.");
    }
}
