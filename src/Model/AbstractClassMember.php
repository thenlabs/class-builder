<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

use ThenLabs\ClassBuilder\Helpers;
use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\ClassBuilder\Exception\InvalidMemberNameException;
use ThenLabs\ClassBuilder\Exception\InvalidMethodNameException;
use ThenLabs\ClassBuilder\Exception\InvalidConstantNameException;
use ThenLabs\ClassBuilder\Exception\InvalidPropertyNameException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
abstract class AbstractClassMember
{
    protected $name;
    protected $builder;

    public function __construct(string $name)
    {
        $this->setName($name);
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

    public function setName(string $name): void
    {
        if (! Helpers::validNameForClassMember($name)) {
            if ($this instanceof Constant) {
                throw new InvalidConstantNameException($name);
            } elseif ($this instanceof Property) {
                throw new InvalidPropertyNameException($name);
            } elseif ($this instanceof Method) {
                throw new InvalidMethodNameException($name);
            } else {
                throw new InvalidMemberNameException($name);
            }
        }

        $this->name = $name;
    }
}
