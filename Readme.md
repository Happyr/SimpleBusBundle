# SimpleBusBundle

[![Latest Version](https://img.shields.io/github/release/Happyr/SimpleBusBundle.svg?style=flat-square)](https://github.com/Happyr/SimpleBusBundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/Happyr/SimpleBusBundle.svg?style=flat-square)](https://travis-ci.org/Happyr/SimpleBusBundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Happyr/SimpleBusBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/Happyr/SimpleBusBundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/Happyr/SimpleBusBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/Happyr/SimpleBusBundle)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/simplebus-bundle.svg?style=flat-square)](https://packagist.org/packages/happyr/simplebus-bundle)


This bundle includes all the nice extra features Happyr needs for their SimpleBus installation. The purpose is not to be
100% resuable and flexible. Feel free to for it and adjust it for your needs. 

### Installation

```
composer require happyr/simplebus-bundle
```

```php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Happyr\SimpleBusBundle\HappyrSimpleBusBundle(), // <-- Make sure this is before the SimpleBusBrige bundles. 
            new Happyr\Mq2phpBundle\HappyrMq2phpBundle(),
            new SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
            new SimpleBus\SymfonyBridge\SimpleBusEventBusBundle(),
            new SimpleBus\AsynchronousBundle\SimpleBusAsynchronousBundle(),
            new SimpleBus\RabbitMQBundleBridge\SimpleBusRabbitMQBundleBridgeBundle(),
            new SimpleBus\JMSSerializerBundleBridge\SimpleBusJMSSerializerBundleBridgeBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
			new \SimpleBus\JMSSerializerBundleBridge\SimpleBusJMSSerializerBundleBridgeBundle(),
        ];
        // ...
    }
    // ...
}
```

```yaml
# /app/config/happyr_simplebus.yml

parameters:
  app.command_queue: 'commands'
  app.event_queue: 'events'
  simple_bus.command_bus.logging.level: info
  simple_bus.event_bus.logging.level: info

happyr_mq2php:
  enabled: true
  secret_key: 'CHANGE_ME'
  command_queue: "%app.command_queue%"
  event_queue: "%app.event_queue%"
  message_headers:
    fastcgi_host: "%fastcgi_host%"
    fastcgi_port: "%fastcgi_port%"
    dispatch_path: "%mq2php_dispatch_path%"

command_bus:
  logging: ~

event_bus:
  logging: ~

simple_bus_rabbit_mq_bundle_bridge:
  commands:
    # this producer service will be defined by OldSoundRabbitMqBundle,
    # its name is old_sound_rabbit_mq.%producer_name%_producer
    producer_service_id: old_sound_rabbit_mq.asynchronous_commands_producer
  events:
    # this producer service will be defined by OldSoundRabbitMqBundle,
    # its name is old_sound_rabbit_mq.%producer_name%_producer
    producer_service_id: old_sound_rabbit_mq.asynchronous_events_producer

simple_bus_asynchronous:
  events:
    strategy: 'predefined'

old_sound_rabbit_mq:
  connections:
    default:
      host:     "%rabbitmq_host%"
      port:     5672
      user:     'guest'
      password: 'guest'
      vhost:    '/'
      lazy:     false
      connection_timeout: 3
      read_write_timeout: 3

      # requires php-amqplib v2.4.1+ and PHP5.4+
      keepalive: false

      # requires php-amqplib v2.4.1+
      heartbeat: 0
  producers:
    asynchronous_commands:
      connection:       default
      exchange_options: { name: '%app.command_queue%', type: "x-delayed-message", arguments: {"x-delayed-type": ["S","direct"]} }
      queue_options:    { name: "%app.command_queue%", durable: true }

    asynchronous_events:
      connection:       default
      exchange_options: { name: '%app.event_queue%', type: "x-delayed-message", arguments: {"x-delayed-type": ["S","direct"]} }
      queue_options:    { name: "%app.event_queue%", durable: true }
```

Continue to read at [Mq2phpBundle](https://github.com/Happyr/Mq2phpBundle). Make sure to install the [RabbitMQ extension](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange)
for delayed messages. 

### Use

Create your messages in 
```
src/App/Message
  Command
  CommandHandler
  Event
  EventSubscriber
```
And they will be auto wired and registered automatically. You may of course register them manually. 

#### Classes & Interfaces

Be aware of the following base classes. 

* `BaseCommandHandler`   
* `BaseEventSubscriber` implements `AutoRegisteredEventSubscriber`
* `HandlesMessagesAsync` (For async handlers/subscribers)
* `DelayedMessage` (For async messages with a delay)

### Direct publisher

If do not want to use a queue you may use the direct publisher.

```php
happyr_simplebus:
  use_direct_publisher: true

```

