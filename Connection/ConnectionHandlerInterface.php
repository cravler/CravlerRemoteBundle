<?php

namespace Cravler\RemoteBundle\Connection;

use Cravler\RemoteBundle\Security\Token;
use Cravler\RemoteBundle\Proxy\RemoteProxy;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface ConnectionHandlerInterface
{
    const TYPE_CONNECT = 'connect';
    const TYPE_DISCONNECT = 'disconnect';

    /**
     * @param $type
     * @param Token $token
     * @param RemoteProxy $remote
     */
    public function handle($type, Token $token, RemoteProxy $remote);
}
