<?php
declare(strict_types=1);

namespace NubecuLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Property extends AbstractClassMember
{
    use CommentTrait;
    use ValueTrait { setValue as setDefaultValue; }
    use AccessTrait;
    use StaticTrait;

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

        return "{$comments} {$this->access} {$static} \${$this->name} {$value};";
    }
}
