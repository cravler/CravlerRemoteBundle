<?php

namespace Cravler\RemoteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EndpointsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cravler_remote.service.endpoints_chain')) {
            return;
        }

        $definition = $container->getDefinition(
            'cravler_remote.service.endpoints_chain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'cravler_remote.endpoint'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addEndpoint',
                array(new Reference($id))
            );
        }
    }
}
