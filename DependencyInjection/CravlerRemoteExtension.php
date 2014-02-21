<?php

namespace Cravler\RemoteBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class CravlerRemoteExtension extends Extension
{
    const CONFIG_KEY = 'cravler_remote.config';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY . '.' . $key, $value);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if(in_array($container->getParameter('kernel.environment'), array('test', 'dev'))) {
            $loader->load('services_dev.xml');
        }

        if (false !== $config['user_provider']) {
            $definition = $container->getDefinition('cravler_remote.security.token_factory');
            $definition->replaceArgument(1, new Reference($config['user_provider']));
        }
    }
}
