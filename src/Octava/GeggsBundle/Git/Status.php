<?php
namespace Octava\GeggsBundle\Git;

use Symfony\Component\Process\Process;

/**
 * Class Status
 * @package Octava\GeggsBundle\Git
 */
class Status extends AbstractGit
{
    /**
     * @param string $dir
     * @return Process
     */
    public function run($dir)
    {
        $cmd = $this->buildCommand($dir, ['status']);
        $process = $this->runCommand($cmd);

        return $process;
    }
}
