<?php

namespace Cravler\RemoteBundle\Service;

use Cravler\RemoteBundle\Connection\ConnectionHandlerInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class ConnectionHandlersChain
{
    /**
     * @var array
     */
    private $handlers = array();

    /**
     * @param $handler
     */
    public function addHandler($handler)
    {
        if ($handler instanceof ConnectionHandlerInterface) {
            $this->handlers[] = $handler;
        }
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
} 