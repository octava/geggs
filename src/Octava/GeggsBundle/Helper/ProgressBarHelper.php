<?php
namespace Octava\GeggsBundle\Helper;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProgressBarHelper
{
    /**
     * @var ProgressBar
     */
    protected $progressBar = null;
    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle = null;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->setSymfonyStyle($symfonyStyle);
    }

    public function advance($message)
    {
        if ($this->getProgressBar() instanceof ProgressBar) {
            $this->getProgressBar()->setMessage($message);
            $this->getProgressBar()->advance();
        }
    }

    public function finish()
    {
        if ($this->getProgressBar() instanceof ProgressBar) {
            $this->getProgressBar()->finish();
            $this->getProgressBar()->clear();
        }
    }

    /**
     * @return ProgressBar
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }

    /**
     * @param ProgressBar $progressBar
     * @return self
     */
    public function setProgressBar($progressBar)
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    /**
     * @return SymfonyStyle
     */
    public function getSymfonyStyle()
    {
        return $this->symfonyStyle;
    }

    /**
     * @param SymfonyStyle $symfonyStyle
     * @return self
     */
    public function setSymfonyStyle($symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;

        return $this;
    }

    /**
     * @param int $count
     * @return ProgressBar
     */
    public function create($count)
    {
        if ($this->getSymfonyStyle()->getVerbosity() == OutputStyle::VERBOSITY_NORMAL) {
            $progressBar = $this->getSymfonyStyle()->createProgressBar($count);
            $progressBar->setFormat(
                " %message%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%"
            );

            $this->setProgressBar($progressBar);
        }

        return $this->getProgressBar();
    }
}
