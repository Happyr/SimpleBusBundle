<?php

namespace Happyr\SimpleBusBundle\DependencyInjection\CompilerPass;

use Happyr\SimpleBusBundle\Message\Publisher\RabbitMQPublisher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Tobias Nyholm
 */
class CompilerPasses implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->removeCommandHandlerDuplicates($container);
        $this->handleEventSubscriberDuplicates($container);
        $this->processMessageHandlers($container);
        $this->replaceSimpleBusPublisher($container);
    }

    /**
     * If we find to command handler services that are registered on the same command, make sure we remove the one with '.auto' on the end.
     *
     * @param ContainerBuilder $container
     */
    private function removeCommandHandlerDuplicates(ContainerBuilder $container)
    {
        $taggedServices = array_merge($container->findTaggedServiceIds('command_handler'), $container->findTaggedServiceIds('asynchronous_command_handler'));

        $commands = [];

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($commands[$tag['handles']])) {
                    // Find the one that ends with '.auto'
                    $removeServiceId = null;

                    if (substr($id, -5) === '.auto') {
                        $removeServiceId = $id;
                    }

                    if (substr($commands[$tag['handles']], -5) === '.auto') {
                        $removeServiceId = $commands[$tag['handles']];
                        $commands[$tag['handles']] = $id;
                    }

                    if ($removeServiceId !== null) {
                        // Remove the definition
                        $container->removeDefinition($removeServiceId);
                        continue;
                    }
                }
                $commands[$tag['handles']] = $id;
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function handleEventSubscriberDuplicates(ContainerBuilder $container)
    {
        $taggedServices = array_merge($container->findTaggedServiceIds('event_subscriber'), $container->findTaggedServiceIds('asynchronous_event_subscriber'));

        // Keys are event class names
        $events = [];

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['subscribes_to'])) {
                    $events[$tag['subscribes_to']][] = $id;
                }
            }
        }

        foreach ($events as $eventClass => $subscribersIds) {
            if (count($subscribersIds) <= 1) {
                continue;
            }

            // Get services
            $subscriberClassNames = [];
            foreach ($subscribersIds as $subscribersId) {
                $service = $container->getDefinition($subscribersId);
                $subscriberClassNames[$service->getClass()][] = $subscribersId;
            }

            foreach ($subscriberClassNames as $className => $services) {
                if (count($services) <= 1) {
                    continue;
                }

                // IF we have multiple services registed to the same event subscriber, remove the auto added ones.
                foreach ($services as $serviceId) {
                    if (substr($serviceId, -5) === '.auto') {
                        $container->removeDefinition($serviceId);
                    }
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processMessageHandlers(ContainerBuilder $container)
    {
        $taggedServices = array_merge(
            $container->findTaggedServiceIds('command_handler'),
            $container->findTaggedServiceIds('event_subscriber'),
            $container->findTaggedServiceIds('asynchronous_command_handler'),
            $container->findTaggedServiceIds('asynchronous_event_subscriber')
        );
        $doctrine = $this->hasDoctrine($container);

        foreach ($taggedServices as $id => $tags) {
            $def = $container->findDefinition($id);
            $class = $def->getClass();

            if (method_exists($class, 'setEventRecorder')) {
                $def->addMethodCall('setEventRecorder', array(new Reference('event_recorder')));
            }

            if (method_exists($class, 'setCommandBus')) {
                $def->addMethodCall('setCommandBus', array(new Reference('command_bus')));
            }

            if (method_exists($class, 'setEventBus')) {
                $def->addMethodCall('setEventBus', array(new Reference('event_bus')));
            }

            if ($doctrine && method_exists($class, 'setEntityManager')) {
                $def->addMethodCall('setEntityManager', array(new Reference('doctrine.orm.entity_manager')));
            }

            if (in_array('Psr\Log\LoggerAwareInterface', class_implements($class))) {
                $def->addMethodCall('setLogger', array(new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE)));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function replaceSimpleBusPublisher(ContainerBuilder $container)
    {
        if ($container->has('simple_bus.rabbit_mq_bundle_bridge.event_publisher')) {
            $container->getDefinition('simple_bus.rabbit_mq_bundle_bridge.event_publisher')
                ->setClass(RabbitMQPublisher::class)
                ->setLazy(true);
            $container->getDefinition('simple_bus.rabbit_mq_bundle_bridge.command_publisher')
                ->setClass(RabbitMQPublisher::class)
                ->setLazy(true);
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return bool
     */
    private function hasDoctrine(ContainerBuilder $container)
    {
        return $container->hasDefinition('doctrine.orm.entity_manager') ||
            $container->hasAlias('doctrine.orm.entity_manager');
    }
}
