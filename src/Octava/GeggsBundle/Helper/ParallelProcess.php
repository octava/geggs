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
     * ParallelProcess constructor
     * @param SymfonyStyle $symfonyStyle
     */
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->parallelProcessRunner = new ParallelProcessRunner();
        $this->parallelProcessRunner->setMaxParallelProcess(5);
        $this->parallelProcessRunner->setStatusCheckWait(15);
        $this->parallelProcessRunner->getEventDispatcher()->addSubscriber(new ParallelProcessSubscriber($symfonyStyle));
    }

    /**
     * @return SymfonyStyle
     */
    public function getSymfonyStyle()
    {
        return $this->symfonyStyle;
    }

    /**
     * @param string $cmd
     * @param bool   $isDryRun
     * @param bool   $tty
     * @return $this
     */
    public function add($cmd, $isDryRun = false, $tty = false)
    {
        $process = null;
        if (!$isDryRun) {
            $process = new Process($cmd);
            $process->setTty($tty);
            $this->parallelProcessRunner->add($process);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function run()
    {
        return $this->parallelProcessRunner->run();
    }
}
