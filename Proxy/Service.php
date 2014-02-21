<?php

namespace Cravler\RemoteBundle\Proxy;

use Cravler\RemoteBundle\Service\EndpointsChain;
use Cravler\RemoteBundle\Service\ConnectionHandlersChain;
use Cravler\RemoteBundle\Security\TokenFactory;
use Cravler\RemoteBundle\Security\Authorization\Storage;
use Cravler\RemoteBundle\Connection\ConnectionHandlerInterface;
use Cravler\RemoteBundle\Proxy\RemoteProxy;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Service
{
    /**
     * @var TokenFactory
     */
    private $factory;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var EndpointsChain
     */
    private $endpointsChain;

    /**
     * @var ConnectionHandlersChain
     */
    private $connectionHandlersChain;

    /**
     * @param TokenFactory $factory
     * @param Storage $storage
     * @param EndpointsChain $endpointsChain
     * @param ConnectionHandlersChain $connectionHandlersChain
     */
    public function __construct(TokenFactory $factory, Storage $storage, EndpointsChain $endpointsChain, ConnectionHandlersChain $connectionHandlersChain)
    {
        $this->factory                 = $factory;
        $this->storage                 = $storage;
        $this->endpointsChain          = $endpointsChain;
        $this->connectionHandlersChain = $connectionHandlersChain;
    }

    /**
     * @param $cb
     */
    public function ping($cb = null)
    {
        if (is_callable($cb)) {
            $cb();
        }
    }

    /**
     * @param $authToken
     * @param $remoteToken
     * @param $type
     * @param $cb
     */
    public function handle($authToken = array(), $remoteToken = array(), $type = null, $cb = null)
    {
        $authToken = json_decode(json_encode($authToken), true);
        if (!$this->storage->tokenExists((array) $authToken)) {
            return;
        }

        $handlers = $this->connectionHandlersChain->getHandlers();

        $this->remote->userToken($remoteToken, function($token) use ($handlers, $type, $cb) {
            $token = json_decode(json_encode($token), true);
            $token = $this->factory->createToken((array) $token);

            foreach ($handlers as $handler) {
                /* @var ConnectionHandlerInterface $handler */
                $handler->handle($type, $token, new RemoteProxy($this->remote));
            }

            if (is_callable($cb)) {
                $cb();
            }
        });
    }

    /**
     * @param $authToken
     * @param $remoteToken
     * @param $cb
     */
    public function endpoints($authToken = array(), $remoteToken = array(), $cb = null)
    {
        $authToken = json_decode(json_encode($authToken), true);
        if (!$this->storage->tokenExists((array) $authToken)) {
            return;
        }

        if (is_callable($cb)) {
            $cb($this->endpointsChain->getKeys());
        }
    }

    /**
     * @param $authToken
     * @param $remoteToken
     * @param $name
     * @param $arguments
     */
    public function call($authToken = array(), $remoteToken = array(), $name = null, $arguments = array())
    {
        $authToken = json_decode(json_encode($authToken), true);
        if (!$this->storage->tokenExists((array) $authToken)) {
            return;
        }

        $method   = substr(strrchr($name, '.'), 1);
        $endpoint = $this->endpointsChain->getEndpoint(str_replace('.' . $method, '', $name));

        $userTokenKey = false;
        $arguments = (array) $arguments;
        $rfl = new \ReflectionMethod($endpoint, $method);
        foreach ($rfl->getParameters() as $key => $param) {
            $clazz = $param->getClass();
            if ($clazz) {
                $className = $clazz->getName();
                if ('Cravler\RemoteBundle\Security\Token' == $className) {
                    $userTokenKey = $key;
                }

                if ('Cravler\RemoteBundle\Proxy\RemoteProxy' == $className) {
                    array_splice($arguments, $key, 0, array(new RemoteProxy($this->remote)));
                }
            }
        }

        if (false !== $userTokenKey) {
            $this->remote->userToken($remoteToken, function($token) use ($endpoint, $method, $arguments, $userTokenKey) {
                $token = json_decode(json_encode($token), true);
                $token = $this->factory->createToken((array) $token);
                array_splice($arguments, $userTokenKey, 0, array($token));
                call_user_func_array(array($endpoint, $method), $arguments);
            });
        } else {
            call_user_func_array(array($endpoint, $method), $arguments);
        }
    }
}