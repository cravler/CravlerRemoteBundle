<?php

namespace Cravler\RemoteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cravler_remote');

        $rootNode
            ->children()
                ->scalarNode('user_provider')
                    ->defaultValue(false) //security.user.provider.concrete.[provider_name]
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('app_port')
                    ->defaultValue(8080)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('remote_port')
                    ->defaultValue(8081)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('remote_host')
                    ->defaultValue('127.0.0.1')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('server_port')
                    ->defaultValue(8082)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('server_host')
                    ->defaultValue('127.0.0.1')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('secret')
                    ->defaultValue('ThisTokenIsNotSoSecretChangeIt')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
