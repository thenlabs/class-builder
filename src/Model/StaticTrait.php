<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait StaticTrait
{
    protected $static = false;

    public function setStatic(bool $static): self
    {
        $this->static = $static;

        return $this;
    }
}
