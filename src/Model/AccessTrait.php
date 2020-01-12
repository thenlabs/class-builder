<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Model;

use NubecuLabs\ClassBuilder\Exception\InvalidAccessException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait AccessTrait
{
    protected $access = 'public';

    public function setAccess(string $access): self
    {
        if (! in_array($access, ['public', 'private', 'protected'])) {
            throw new InvalidAccessException($access);
        }

        $this->access = $access;

        return $this;
    }
}
