<?php
namespace Octava\GeggsBundle\Provider;

use Octava\GeggsBundle\Config;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

abstract class AbstractProvider
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var string
     */
    protected $repositoryPath;

    /**
     * AbstractGit constructor.
     * @param Config $config
     * @param string $repositoryPath
     */
    public function __construct(Config $config, $repositoryPath)
    {
        $this->config = $config;
        $this->repositoryPath = $repositoryPath;
    }

    /**
     * @return string
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function run($command, array $arguments = [])
    {
        $cmd = $this->buildCommand($command, $arguments);
        $process = $this->runCommand($cmd);
        $result = trim($process->getOutput());

        return $result;
    }

    /**
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function buildCommand($command, array $arguments = [])
    {
        array_unshift($arguments, $command);
        $bin = $config = $this->config->getGitBin();
        $builder = ProcessBuilder::create($arguments);
        $builder->setPrefix($bin);

        $cmd = $builder->getProcess()->getCommandLine();
        $cmd = 'cd '.$this->getRepositoryPath().' && '.$cmd;

        return $cmd;
    }

    /**
     * @param string $cmd
     * @return Process
     */
    public function runCommand($cmd)
    {
        $process = new Process($cmd);
//        $process->setTty(true);
        $process->mustRun();

        return $process;
    }
}
