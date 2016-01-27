<?php
namespace Octava\GeggsBundle\Provider;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitProvider extends AbstractProvider
{
    /**
     * @param string $cmd
     * @return Process
     */
    public function runCommand($cmd)
    {
        try {
            $process = parent::runCommand($cmd);
        } catch (ProcessFailedException $e) {
            $message = $e->getMessage();
            if (false !== strpos($message, 'nothing to commit')) {
                throw $e;
            }
        }

        return $process;
    }
}
