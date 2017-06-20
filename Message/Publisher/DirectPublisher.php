<?php

namespace Happyr\SimpleBusBundle\Message\Publisher;

use Happyr\Mq2phpBundle\Service\ConsumerWrapper;
use Happyr\SimpleBusBundle\Message\DelayedMessage;
use SimpleBus\Asynchronous\Publisher\Publisher;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;

class DirectPublisher implements Publisher
{
    /**
     * @var ConsumerWrapper
     */
    private $consumer;

    /**
     * @var MessageInEnvelopSerializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $queueName;

    private $messages = [];

    private $delayedMessages = [];

    /**
     *
     * @param ConsumerWrapper $consumer
     */
    public function __construct(ConsumerWrapper $consumer, MessageInEnvelopSerializer $serializer, string $queueName)
    {
        $this->consumer = $consumer;
        $this->serializer = $serializer;
        $this->queueName = $queueName;
    }

    public function publish($message)
    {
        if ($message instanceof DelayedMessage) {
            $this->delayedMessages[$message->getDelayedTime()][] = $this->serializer->wrapAndSerialize($message);
        } else {
            $this->messages[] = $this->serializer->wrapAndSerialize($message);
        }
    }

    public function consume()
    {
        foreach ($this->messages as $message) {
            $this->doConsume($message);
        }

        ksort($this->delayedMessages);
        foreach ($this->delayedMessages as $messages) {
            foreach ($messages as $message) {
                $this->doConsume($message);
            }
        }
    }

    public function __destruct()
    {
        $this->consume();
    }

    /**
     * @param string $data
     */
    private function doConsume(string $data)
    {
        $message = json_decode($data, true);
        $body = $message['body'];

        $this->consumer->consume($this->queueName, $body);
    }
}
