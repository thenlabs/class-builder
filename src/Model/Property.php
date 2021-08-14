<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

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
        $static = $this->static ? 'static' : '';
        $value = " = " . var_export($this->value, true);

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
        $this->type = $type;

        return $this;
    }
}
