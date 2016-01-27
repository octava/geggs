<?php
namespace Octava\GeggsBundle\Git;

use Symfony\Component\Process\Process;

/**
 * Class Commit
 * @package Octava\GeggsBundle\Git
 */
class Commit extends AbstractGit
{
    /**
     * @param string $dir
     * @return Process
     */
    public function run($dir)
    {
        $cmd = $this->buildCommand($dir, ['commit']);
        $process = $this->runCommand($cmd);

        return $process;
    }
}
