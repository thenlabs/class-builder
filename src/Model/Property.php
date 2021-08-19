<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\ClassBuilder\Exception\UnsupportedFeatureException;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Property extends AbstractClassMember
{
    use CommentTrait;
    use ValueTrait { setValue as setDefaultValue; }
    use AccessTrait;
    use StaticTrait;

    protected $type;

    public function getCode(): string
    {
        if ($this->builder->getEntityType() === ClassBuilder::ENTITY_INTERFACE) {
            return '';
        }

        $static = $this->static ? 'static' : '';

        $value = $this->hasDefaultValue ? " = " . var_export($this->value, true) : '';

        $comments = $this->comments ? $this->comments : '';
        if (! $comments) {
            $comments = "/**\n";
            foreach ($this->commentLines as $comment) {
                $comments .= " * {$comment}\n";
            }
            $comments .= " */";
        }

        $typeStr = '';
        if ($this->type) {
            $typeStr = class_exists($this->type) ? "\\{$this->type}" : $this->type;
        }

        return "{$comments} {$this->access} {$static} {$typeStr} \${$this->name} {$value};";
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            throw new UnsupportedFeatureException('The typed properties are only supported from PHP 7.4');
        }

        $this->type = $type;

        return $this;
    }
}
