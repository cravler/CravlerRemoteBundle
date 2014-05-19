<?php

namespace Cravler\RemoteBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class PingHandlersCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cravler_remote.service.ping_handlers_chain')) {
            return;
        }

        $definition = $container->getDefinition(
            'cravler_remote.service.ping_handlers_chain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'cravler_remote.ping_handler'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addHandler',
                array(new Reference($id))
            );
        }
    }
}
