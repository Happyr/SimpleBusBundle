<?php

namespace Happyr\SimpleBusBundle\Tests\Functional;

use Happyr\Mq2phpBundle\HappyrMq2phpBundle;
use Happyr\SimpleBusBundle\HappyrSimpleBusBundle;
use Happyr\SimpleBusBundle\Message\Publisher\RabbitMQPublisher;
use JMS\SerializerBundle\JMSSerializerBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use OldSound\RabbitMqBundle\OldSoundRabbitMqBundle;
use SimpleBus\AsynchronousBundle\SimpleBusAsynchronousBundle;
use SimpleBus\JMSSerializerBundleBridge\SimpleBusJMSSerializerBundleBridgeBundle;
use SimpleBus\RabbitMQBundleBridge\SimpleBusRabbitMQBundleBridgeBundle;
use SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle;
use SimpleBus\SymfonyBridge\SimpleBusEventBusBundle;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return HappyrSimpleBusBundle::class;
    }

    public function testInitBundle()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config/framework.yml');
        $kernel->addConfigFile(__DIR__.'/config/happyr_simplebus.yml');

        // Add some other bundles we depend on
        $kernel->addBundle(HappyrMq2phpBundle::class);
        $kernel->addBundle(SimpleBusCommandBusBundle::class);
        $kernel->addBundle(SimpleBusEventBusBundle::class);
        $kernel->addBundle(SimpleBusAsynchronousBundle::class);
        $kernel->addBundle(SimpleBusRabbitMQBundleBridgeBundle::class);
        $kernel->addBundle(SimpleBusJMSSerializerBundleBridgeBundle::class);
        $kernel->addBundle(OldSoundRabbitMqBundle::class);
        $kernel->addBundle(JMSSerializerBundle::class);

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('simple_bus.rabbit_mq_bundle_bridge.event_publisher'));
        $service = $container->get('simple_bus.rabbit_mq_bundle_bridge.event_publisher');
        $this->assertInstanceOf(RabbitMQPublisher::class, $service);
    }
}
