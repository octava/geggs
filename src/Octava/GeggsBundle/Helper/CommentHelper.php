<?php
namespace Octava\GeggsBundle\Helper;

class CommentHelper
{
    public static function buildComment($comment, $branch)
    {
        $result = $comment;
        if (!preg_match('/^\w+-\d+:/i', $comment)) {
            $result = $branch.': '.$comment;
        }

        return $result;
    }
}
