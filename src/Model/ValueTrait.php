<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait ValueTrait
{
    protected $value;

    protected $hasDefaultValue = false;

    public function setValue($value): self
    {
        $this->value = $value;

        $this->hasDefaultValue = true;

        return $this;
    }
}
