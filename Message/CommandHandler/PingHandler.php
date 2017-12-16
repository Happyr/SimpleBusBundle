<?php

namespace Happyr\SimpleBusBundle\Message\CommandHandler;

use Psr\Log\LoggerInterface;
use Happyr\SimpleBusBundle\Message\Command\Ping;

class PingHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function handle(Ping $command)
    {
        $data = $command->getData();

        if (null !== $this->logger) {
            $this->logger->error('Ping command handler works!', ['data' => $data]);
        }

        return;
    }
}
