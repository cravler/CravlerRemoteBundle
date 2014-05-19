<?php

namespace Cravler\RemoteBundle\Service;

use Cravler\RemoteBundle\Connection\PingHandlerInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class PingHandlersChain
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
        if ($handler instanceof PingHandlerInterface) {
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