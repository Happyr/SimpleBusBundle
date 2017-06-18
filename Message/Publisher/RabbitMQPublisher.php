<?php

namespace Happyr\SimpleBusBundle\Message\Publisher;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use OldSound\RabbitMqBundle\RabbitMq\Fallback;
use Happyr\SimpleBusBundle\Message\DelayedMessage;
use SimpleBus\Asynchronous\Properties\AdditionalPropertiesResolver;
use SimpleBus\Asynchronous\Publisher\Publisher;
use SimpleBus\Asynchronous\Routing\RoutingKeyResolver;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;

/**
 * This publisher supports delayed messages.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RabbitMQPublisher implements Publisher
{
    /**
     * @var MessageInEnvelopSerializer
     */
    private $serializer;

    /**
     * @var Producer|Fallback
     */
    private $producer;

    /**
     * @var RoutingKeyResolver
     */
    private $routingKeyResolver;

    /**
     * @var AdditionalPropertiesResolver
     */
    private $additionalPropertiesResolver;

    public function __construct(
        MessageInEnvelopSerializer $messageSerializer,
        $producer,
        RoutingKeyResolver $routingKeyResolver,
        AdditionalPropertiesResolver $additionalPropertiesResolver
    ) {
        if (!$producer instanceof Producer && !$producer instanceof Fallback) {
            throw new \LogicException('Producer must be an instance of OldSound\RabbitMqBundle\RabbitMq\Producer or OldSound\RabbitMqBundle\RabbitMq\Fallback');
        }

        $this->serializer = $messageSerializer;
        $this->producer = $producer;
        $this->routingKeyResolver = $routingKeyResolver;
        $this->additionalPropertiesResolver = $additionalPropertiesResolver;
    }

    /**
     * Publish the given Message by serializing it and handing it over to a RabbitMQ producer.
     *
     * {@inheritdoc}
     */
    public function publish($message)
    {
        $serializedMessage = $this->serializer->wrapAndSerialize($message);
        $routingKey = $this->routingKeyResolver->resolveRoutingKeyFor($message);
        $additionalProperties = $this->additionalPropertiesResolver->resolveAdditionalPropertiesFor($message);

        $headers = [];
        if ($message instanceof DelayedMessage) {
            $headers['x-delay'] = $message->getDelayedTime();
        }

        $this->producer->publish($serializedMessage, $routingKey, $additionalProperties, $headers);
    }
}
