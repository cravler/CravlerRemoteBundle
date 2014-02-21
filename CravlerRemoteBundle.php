<?php

namespace Cravler\RemoteBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Cravler\RemoteBundle\DependencyInjection\Compiler\RoomsCompilerPass;
use Cravler\RemoteBundle\DependencyInjection\Compiler\EndpointsCompilerPass;
use Cravler\RemoteBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Cravler\RemoteBundle\DependencyInjection\Compiler\ConnectionHandlersCompilerPass;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class CravlerRemoteBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RoomsCompilerPass);
        $container->addCompilerPass(new EndpointsCompilerPass);
        $container->addCompilerPass(new GlobalVariablesCompilerPass);
        $container->addCompilerPass(new ConnectionHandlersCompilerPass);
    }
}
