<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
trait CommentTrait
{
    protected $comments;
    protected $commentLines = [];

    public function setDocComment(string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function addComment(string $comment): self
    {
        $this->commentLines[] = $comment;

        return $this;
    }

    public function addComments(string ...$comments): self
    {
        foreach ($comments as $comment) {
            $this->addComment($comment);
        }

        return $this;
    }
}
