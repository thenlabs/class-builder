<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait AbstractTrait
{
    protected $abstract = false;

    public function setAbstract(bool $abstract): self
    {
        $this->abstract = $abstract;

        return $this;
    }
}
