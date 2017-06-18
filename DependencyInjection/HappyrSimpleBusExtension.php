<?php

namespace Happyr\SimpleBusBundle\DependencyInjection;

use Happyr\SimpleBusBundle\Message\AutoRegisteredEventSubscriber;
use Happyr\SimpleBusBundle\Message\HandlesMessagesAsync;
use Money\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HappyrSimpleBusExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('ping.yml');
        $this->requireBundle('SimpleBusCommandBusBundle', $container);
        $this->requireBundle('SimpleBusEventBusBundle', $container);
        $this->requireBundle('SimpleBusAsynchronousBundle', $container);
        $this->requireBundle('SimpleBusRabbitMQBundleBridgeBundle', $container);
        $this->requireBundle('SimpleBusJMSSerializerBundleBridgeBundle', $container);
        $this->requireBundle('HappyrMq2phpBundle', $container);
        $this->requireBundle('OldSoundRabbitMqBundle', $container);
        $this->requireBundle('JMSSerializerBundle', $container);

        if ($config['auto_register_handlers']['enabled']) {
            $handlerPath = $config['auto_register_handlers']['command_handler_path'];
            if (empty($handlerPath)) {
                $rootDir = $container->getParameter('kernel.root_dir');
                $handlerPath = $rootDir.'/../src/App/Message/CommandHandler';
            }

            $commandNamespace = $config['auto_register_handlers']['command_namespace'];
            if (empty($commandNamespace)) {
                $commandNamespace = 'App\\Message\\Command';
            }

            $handlerNamespace = $config['auto_register_handlers']['command_handler_namespace'];
            if (empty($handlerNamespace)) {
                $handlerNamespace = 'App\\Message\\CommandHandler';
            }

            $this->autoRegisterCommands($container, $commandNamespace, $handlerNamespace, $handlerPath);
        }

        if ($config['auto_register_event_subscribers']['enabled']) {
            $path = $config['auto_register_event_subscribers']['event_subscriber_path'];
            if (empty($path)) {
                $rootDir = $container->getParameter('kernel.root_dir');
                $path = $rootDir.'/../src/App/Message/EventSubscriber';
            }

            $namespace = $config['auto_register_event_subscribers']['event_subscriber_namespace'];
            if (empty($namespace)) {
                $namespace = 'App\\Message\\EventSubscriber';
            }

            $this->autoRegisterEventSubscribers($container, $namespace, $path);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $commandNamespace
     * @param string           $handlerNamespace
     * @param string           $handlerPath
     */
    protected function autoRegisterCommands(ContainerBuilder $container, $commandNamespace, $handlerNamespace, $handlerPath)
    {
        // Make sure it ends with slash
        $commandNamespace = rtrim($commandNamespace, '\\').'\\';
        $handlerNamespace = rtrim($handlerNamespace, '\\').'\\';

        $finder = new Finder();
        try {
            $finder->files()->in($handlerPath)->name('*Handler.php');
        } catch (\InvalidArgumentException $e){
            return;
        }

        foreach ($finder as $file) {
            $handlerClassName = $file->getBasename('.php');
            $commandClassName = $file->getBasename('Handler.php');

            $dynamicContainerId = '';
            $dynamicFQN = '';
            $path = $file->getRelativePath();
            if (!empty($path)) {
                $dynamicFQN = str_replace('/', '\\', $path).'\\';
                $dynamicContainerId = str_replace('/', '.', $path).'.';
            }

            $commandFQN = $commandNamespace.$dynamicFQN.$commandClassName;
            $handlerFQN = $handlerNamespace.$dynamicFQN.$handlerClassName;

            $containerId = strtolower(sprintf('command_handler.%s%s.auto', $dynamicContainerId, ltrim(preg_replace('/[A-Z]/', '_$0', $commandClassName), '_')));

            $def = new Definition($handlerFQN);
            $def->setAutowired(true);

            $tag = 'command_handler';
            if (is_subclass_of($handlerFQN, HandlesMessagesAsync::class)) {
                $tag = 'asynchronous_command_handler';
            }

            $def->addTag($tag, ['handles' => $commandFQN]);
            $container->setDefinition($containerId, $def);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $subscriberNamespace
     * @param string           $subscriberPath
     */
    protected function autoRegisterEventSubscribers(ContainerBuilder $container, $subscriberNamespace, $subscriberPath)
    {
        // Make sure it ends with slash
        $subscriberNamespace = rtrim($subscriberNamespace, '\\').'\\';

        $finder = new Finder();
        try {
            $finder->files()->in($subscriberPath)->name('*.php');
        } catch (\InvalidArgumentException $e){
            return;
        }

        foreach ($finder as $file) {
            $subscriberClassName = $file->getBasename('.php');

            $dynamicContainerId = '';
            $dynamicFQN = '';
            $path = $file->getRelativePath();
            if (!empty($path)) {
                $dynamicFQN = str_replace('/', '\\', $path).'\\';
                $dynamicContainerId = str_replace('/', '.', $path).'.';
            }

            $subscriberFQN = $subscriberNamespace.$dynamicFQN.$subscriberClassName;
            if (!is_subclass_of($subscriberFQN, AutoRegisteredEventSubscriber::class)) {
                continue;
            }

            if (!method_exists($subscriberFQN, 'notify')) {
                throw new \LogicException(sprintf('An event subscriber that implements AutoRegisteredEventSubscriber must have a function called notify. Please add one in "%s"', $subscriberFQN));
            }

            $containerId = strtolower(sprintf('event_subscriber.%s%s.auto', $dynamicContainerId, ltrim(preg_replace('/[A-Z]/', '_$0', $subscriberClassName), '_')));
            $def = new Definition($subscriberFQN);
            $def->setAutowired(true);

            $tag = 'event_subscriber';
            if (is_subclass_of($subscriberFQN, HandlesMessagesAsync::class)) {
                $tag = 'asynchronous_event_subscriber';
            }

            $def->addTag($tag, ['subscribes_to' => call_user_func($subscriberFQN.'::subscribesTo')]);
            $container->setDefinition($containerId, $def);
        }
    }

    /**
     * Make sure we have activated the required bundles.
     *
     * @param $bundleName
     * @param ContainerBuilder $container
     */
    private function requireBundle($bundleName, ContainerBuilder $container)
    {
        $enabledBundles = $container->getParameter('kernel.bundles');
        if (!isset($enabledBundles[$bundleName])) {
            throw new \LogicException(sprintf('You need to enable "%s" as well', $bundleName));
        }
    }

    public function getAlias()
    {
        return 'happyr_simplebus';
    }
}
