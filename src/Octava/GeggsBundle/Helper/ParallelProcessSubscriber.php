<?php
namespace Octava\GeggsBundle\Helper;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tonic\ParallelProcessRunner\Event\ParallelProcessRunnerEventType;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * Class ParallelProcessSubscriber
 * @package Octava\GeggsBundle\Helper
 */
class ParallelProcessSubscriber implements EventSubscriberInterface
{
    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;
    /**
     * @var ProgressBarHelper
     */
    protected $progressBarHelper;

    /**
     * ParallelProcessSubscriber constructor.
     * @param SymfonyStyle $symfonyStyle
     */
    public function __construct(SymfonyStyle $symfonyStyle, ProgressBarHelper $progressBarHelper = null)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->progressBarHelper = $progressBarHelper;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ParallelProcessRunnerEventType::PROCESS_STOP_AFTER => ['stopAfter', 0],
        ];
    }

    /**
     * @return SymfonyStyle
     */
    public function getSymfonyStyle()
    {
        return $this->symfonyStyle;
    }

    /**
     * @param ProcessEvent $event
     */
    public function stopAfter(ProcessEvent $event)
    {
        $process = $event->getProcess();

        if (OutputInterface::VERBOSITY_DEBUG <= $this->getSymfonyStyle()->getVerbosity()) {
            $this->getSymfonyStyle()->writeln($process->getCommandLine());
            $error = trim($process->getErrorOutput());
            if ($error) {
                $this->getSymfonyStyle()->writeln($error);
            }
            $output = trim($process->getOutput());
            if ($output) {
                $this->getSymfonyStyle()->writeln($output);
            }
        } else {
            $this->progressBarHelper->advance($process->getWorkingDirectory());
        }
    }
}
