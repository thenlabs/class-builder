<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Constant extends AbstractClassMember
{
    use ValueTrait;
    use AccessTrait;

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function getCode(): string
    {
        return "const {$this->name} = " . var_export($this->value, true) . ';';
    }
}
