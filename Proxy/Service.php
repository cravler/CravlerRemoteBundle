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

        $offset = 0;
        $args = array();
        $userTokenKey = false;
        $rfl = new \ReflectionMethod($endpoint, $method);

        foreach ($rfl->getParameters() as $key => $param) {
            $clazz = $param->getClass();
            $haystack = array(
                'token' => 'Cravler\RemoteBundle\Security\Token',
                'proxy' => 'Cravler\RemoteBundle\Proxy\RemoteProxy',
            );
            if ($clazz && in_array($clazz->getName(), $haystack)) {
                $className = $clazz->getName();
                if ($haystack['token'] == $className) {
                    $userTokenKey = $key;
                    $args[$key] = 'undefined';
                }
                else if ($haystack['proxy'] == $className) {
                    $args[$key] = new RemoteProxy($this->remote);
                }
            } else {
                if (isset($arguments->{$offset})) {
                    $args[$key] = $arguments->{$offset};
                }
                $offset++;

                if (!isset($args[$key])) {
                    if ($param->isDefaultValueAvailable()) {
                        $args[$key] = $param->getDefaultValue();
                    } else if ($param->isArray()) {
                        $args[$key] = array();
                    } else if ($param->isCallable()) {
                        $args[$key] = function() {};
                    } else {
                        $args[$key] = null;
                    }
                } else {
                    if ($param->isArray() && !is_array($args[$key])) {
                        $args[$key] = (array) json_decode(json_encode($args[$key]), true);
                    } else if ($param->isCallable() && !is_callable($args[$key])) {
                        $args[$key] = function() {};
                    }
                }
            }
        }

        if (false !== $userTokenKey) {
            $this->remote->userToken($remoteToken, function($token) use ($endpoint, $method, $args, $userTokenKey) {
                $token = json_decode(json_encode($token), true);
                $token = $this->factory->createToken((array) $token);
                $args[$userTokenKey] = $token;
                call_user_func_array(array($endpoint, $method), $args);
            });
        } else {
            call_user_func_array(array($endpoint, $method), $args);
        }
    }
}