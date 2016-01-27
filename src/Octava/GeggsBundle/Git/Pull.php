<?php
namespace Octava\GeggsBundle\Git;

use Symfony\Component\Process\Process;

/**
 * Class Pull
 * @package Octava\GeggsBundle\Git
 */
class Pull extends AbstractGit
{
    /**
     * @param string $dir
     * @return Process
     */
    public function run($dir)
    {
        $cmd = $this->buildCommand($dir, ['pull']);
        $process = $this->runCommand($cmd);

        return $process;
    }
}
