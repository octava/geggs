<?php
namespace Octava\GeggsBundle\Helper;

/**
 * Class GitOutputHelper
 * @package Octava\GeggsBundle\Helper
 */
class GitOutputHelper
{
    /**
     * @param $subject
     * @return array
     */
    public static function extractLocalBranches($subject)
    {
        $pattern = '/\[(.*)\] /i';
        $matches = null;
        preg_match_all($pattern, $subject, $matches);
        $result = empty($matches[1]) ? [] : $matches[1];

        return $result;
    }

    /**
     * @param $subject
     * @return array
     */
    public static function extractRemoteBranches($subject)
    {
        $pattern = '/\[origin\/(.*)\]/i';
        $matches = null;
        preg_match_all($pattern, $subject, $matches);
        $result = empty($matches[1]) ? [] : $matches[1];

        return $result;
    }

    /**
     * @param $subject
     * @return array
     */
    public static function extractRemoteBranches2($subject)
    {
        $pattern = '/refs\/heads\/(.*)/i';
        $matches = null;
        preg_match_all($pattern, $subject, $matches);
        $result = empty($matches[1]) ? [] : $matches[1];

        return $result;
    }
}
