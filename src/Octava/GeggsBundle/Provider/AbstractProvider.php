<?php
namespace Octava\GeggsBundle\Provider;

use Octava\GeggsBundle\Config;
use Octava\GeggsBundle\Helper\LoggerTrait;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class AbstractProvider
 * @package Octava\GeggsBundle\Provider
 */
abstract class AbstractProvider
{
    use LoggerTrait;

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
     * @param array  $arguments
     * @param bool   $isDryRun
     * @param bool   $tty
     * @return string
     */
    public function run($command, array $arguments = [], $isDryRun = false, $tty = false)
    {
        $cmd = $this->buildCommand($command, $arguments);
        $process = $this->runCommand($cmd, $isDryRun, $tty);

        $result = '';
        if ($process instanceof Process) {
            $result = trim($process->getOutput());
        }

        return $result;
    }

    /**
     * @param string $command
     * @param array  $arguments
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
     * @param bool   $isDryRun
     * @param bool   $tty
     * @return null|Process
     */
    public function runCommand($cmd, $isDryRun = false, $tty = false)
    {
        $this->getLogger()->debug($cmd);

        $process = null;
        if (!$isDryRun) {
            $process = new Process($cmd);
            $process->setTty($tty);
            $process->mustRun();
        }

        return $process;
    }

    /**
     * @param string $branch
     * @return bool
     */
    public function hasLocalBranch($branch)
    {
        $output = $this->run('show-branch', ['--list']);
        $branches = $this->extractBranches($output);
        $result = in_array($branch, $branches);

        return $result;
    }

    /**
     * @param string $branch
     * @return bool
     */
    public function hasRemoteBranch($branch)
    {
        $output = $this->run('show-branch', ['--list', '-r']);
        $branches = $this->extractBranches($output);
        $result = in_array($branch, $branches);

        return $result;
    }

    /**
     * @param string $subject
     * @return array
     */
    protected function extractBranches($subject)
    {
        $pattern = '/ \[origin\/(.*)\] /i';
        $matches = null;
        preg_match_all($pattern, $subject, $matches);
        $result = empty($matches[1]) ? [] : $matches[1];

        return $result;
    }
}
