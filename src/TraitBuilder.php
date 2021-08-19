<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TraitBuilder extends ClassBuilder
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->setEntityType(static::ENTITY_TRAIT);
    }
}