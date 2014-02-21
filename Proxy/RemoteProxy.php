<?php

namespace Cravler\RemoteBundle\Proxy;

use DNode\RemoteProxy as DNodeRemoteProxy;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class RemoteProxy
{
    /**
     * @var DNodeRemoteProxy
     */
    private $remote;

    /**
     * @param DNodeRemoteProxy $remote
     */
    public function __construct(DNodeRemoteProxy $remote)
    {
        $this->remote = $remote;
    }

    public function joinRoom()
    {
        return call_user_func_array(array($this->remote, 'joinRoom'), func_get_args());
    }

    public function dispatch()
    {
        return call_user_func_array(array($this->remote, 'dispatch'), func_get_args());
    }

    public function wait()
    {
        return call_user_func_array(array($this->remote, 'wait'), func_get_args());
    }
}