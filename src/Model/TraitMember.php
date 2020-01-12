<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class TraitMember extends AbstractClassMember
{
    protected $traits;
    protected $definitions = [];

    public function setTraits($traits): void
    {
        $this->traits = $traits;
    }

    public function setDefinitions(array $definitions): void
    {
        $this->definitions = $definitions;
    }

    public function getCode(): string
    {
        $traitsStr = null;

        if (is_string($this->traits)) {
            $traitsStr = $this->traits;
        }

        if (is_array($this->traits)) {
            $traitsStr = implode(',', $this->traits);
        }

        $definitionsStr = ';';
        if (! empty($this->definitions)) {
            $definitionsStr = '{';
            foreach ($this->definitions as $definition) {
                $definitionsStr .= $definition . ';';
            }
            $definitionsStr .= '}';
        }

        return "
            use {$traitsStr} {$definitionsStr}
        ";
    }
}
