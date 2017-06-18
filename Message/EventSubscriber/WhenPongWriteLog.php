<?php

namespace Happyr\SimpleBusBundle\Message\EventSubscriber;

use Psr\Log\LoggerInterface;
use Happyr\SimpleBusBundle\Message\Event\Pong;

class WhenPongWriteLog
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

    public function notify(Pong $event)
    {
        $data = $event->getData();

        if ($this->logger !== null) {
            $this->logger->error('Pong event subscriber works!', ['data' => $data]);
        }

        return;
    }
}
