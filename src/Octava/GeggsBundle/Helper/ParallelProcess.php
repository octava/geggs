<?php
namespace Octava\GeggsBundle\Helper;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * Class ParallelProcess
 * @package Octava\GeggsBundle\Helper
 */
class ParallelProcess
{
    use LoggerTrait;

    /**
     * @var ParallelProcessRunner
     */
    protected $parallelProcessRunner;

    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;

    /**
     * @var ProgressBarHelper
     */
    protected $progressBarHelper;

    /**
     * ParallelProcess constructor
     * @param SymfonyStyle $symfonyStyle
     */
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;

        $this->initProgressBarHelper();
        $this->initParallelProcessRunner();
    }

    /**
     * @return SymfonyStyle
     */
    public function getSymfonyStyle()
    {
        return $this->symfonyStyle;
    }

    /**
     * @param string      $cmd
     * @param string|null $workingDirectory
     * @param bool        $isDryRun
     * @param bool        $tty
     * @return $this
     */
    public function add($cmd, $workingDirectory, $isDryRun = false, $tty = false)
    {
        $process = null;
        if (!$isDryRun) {
            $process = new Process($cmd);
            $process->setTty($tty);
            $process->setWorkingDirectory($workingDirectory);
            $this->parallelProcessRunner->add($process);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function run()
    {
        $reflection = new \ReflectionClass($this->parallelProcessRunner);
        $property = $reflection->getProperty('waitCollection');
        $property->setAccessible(true);
        $waitCollection = $property->getValue($this->parallelProcessRunner);
        $property->setAccessible(false);

        $this->getProgressBarHelper()->create($waitCollection->count());

        $result = $this->parallelProcessRunner->run();

        $this->getProgressBarHelper()->finish();

        return $result;
    }

    /**
     * @return ProgressBarHelper
     */
    public function getProgressBarHelper()
    {
        return $this->progressBarHelper;
    }

    protected function initParallelProcessRunner()
    {
        $this->parallelProcessRunner = new ParallelProcessRunner();
        $this->parallelProcessRunner->setMaxParallelProcess(2);
        $this->parallelProcessRunner->setStatusCheckWait(15);
        $this->parallelProcessRunner->getEventDispatcher()->addSubscriber(
            new ParallelProcessSubscriber($this->getSymfonyStyle(), $this->getProgressBarHelper())
        );
    }

    private function initProgressBarHelper()
    {
        $this->progressBarHelper = new ProgressBarHelper($this->getSymfonyStyle());
    }
}
