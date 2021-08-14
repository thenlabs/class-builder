<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

use ThenLabs\ClassBuilder\ClassBuilder;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
abstract class AbstractClassMember
{
    protected $name;
    protected $builder;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setClassBuilder(ClassBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function end(): ClassBuilder
    {
        return $this->builder;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
