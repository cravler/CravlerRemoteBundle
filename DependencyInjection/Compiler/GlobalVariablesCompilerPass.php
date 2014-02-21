<?php

namespace Cravler\RemoteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Cravler\RemoteBundle\DependencyInjection\CravlerRemoteExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class GlobalVariablesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('twig');
        $parameters = array(
            CravlerRemoteExtension::CONFIG_KEY . '.app_port',
        );
        foreach ($parameters as $key) {
            $def->addMethodCall('addGlobal', array(
                str_replace('.', '_', $key),
                $container->getParameter($key)
            ));
        }
    }
}
