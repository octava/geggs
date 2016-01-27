<?php
namespace Octava\GeggsBundle\Git;

use Octava\GeggsBundle\Config;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class AbstractGit
 * @package Octava\GeggsBundle\Git
 */
class AbstractGit
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractGit constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $dir
     * @param array  $arguments
     * @return string
     */
    protected function buildCommand($dir, array $arguments)
    {
        $bin = $config = $this->config->getGitBin();
        $builder = ProcessBuilder::create($arguments);
        $builder->setPrefix($bin);

        $cmd = $builder->getProcess()
            ->getCommandLine();
        $cmd = 'cd '.$dir.' && '.$cmd;

        return $cmd;
    }

    /**
     * @param $cmd
     * @return Process
     */
    protected function runCommand($cmd)
    {
        $process = new Process($cmd);
        $process->setTty(true);
        try {
            $process->mustRun();

        } catch (ProcessFailedException $e) {
            $message = $e->getMessage();
            if (false !== strpos($message, 'nothing to commit')) {
                throw $e;
            }
        }

        return $process;
    }
}
