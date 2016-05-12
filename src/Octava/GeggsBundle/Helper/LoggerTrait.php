<?php
namespace Octava\GeggsBundle\Helper;

use Monolog\Handler\NullHandler;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class LoggerTrait
 * @package Octava\GeggsBundle\Helper
 */
trait LoggerTrait
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = new Logger(get_called_class());
            $this->logger->pushHandler(new NullHandler());
        }

        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return self
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
