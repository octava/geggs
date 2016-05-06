<?php
namespace Octava\GeggsBundle\Provider;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class GitProvider
 * @package Octava\GeggsBundle\Provider
 */
class GitProvider extends AbstractProvider
{
    /**
     * @param string $cmd
     * @param bool   $isDryRun
     * @param bool   $tty
     * @return null|Process
     */
    public function runCommand($cmd, $isDryRun = false, $tty = false)
    {
        $process = null;
        try {
            $process = parent::runCommand($cmd, $isDryRun, $tty);
        } catch (ProcessFailedException $e) {
            $message = $e->getMessage();
            if (false !== strpos($message, 'nothing to commit')) {
                throw $e;
            }
        }

        return $process;
    }
}
