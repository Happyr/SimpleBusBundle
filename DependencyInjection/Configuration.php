<?php

namespace Happyr\SimpleBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('happyr_simplebus');

        $root->children()
            ->arrayNode('auto_register_handlers')->addDefaultsIfNotSet()->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->scalarNode('command_namespace')->defaultNull()->end()
                ->scalarNode('command_handler_namespace')->defaultNull()->end()
                ->scalarNode('command_handler_path')->defaultNull()->end()
            ->end()->end()
            ->arrayNode('auto_register_event_subscribers')->addDefaultsIfNotSet()->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->scalarNode('event_subscriber_namespace')->defaultNull()->end()
                ->scalarNode('event_subscriber_path')->defaultNull()->end()
            ->end()->end()

        ->end();

        return $treeBuilder;
    }
}
