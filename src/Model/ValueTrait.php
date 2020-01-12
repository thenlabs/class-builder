<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait ValueTrait
{
    protected $value;

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
