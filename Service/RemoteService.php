<?php

namespace Cravler\RemoteBundle\Service;

use DNode\DNode;
use DNode\RemoteProxy as DNodeRemoteProxy;
use React\EventLoop;
use Cravler\RemoteBundle\Proxy\Service;
use Cravler\RemoteBundle\Proxy\RemoteProxy;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class RemoteService
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var int
     */
    private $listenPort;

    /**
     * @var int
     */
    private $connectPort;

    /**
     * @param Service $service
     * @param $listenPort
     * @param $connectPort
     */
    public function __construct(Service $service, $listenPort, $connectPort)
    {
        $this->service     = $service;
        $this->listenPort  = $listenPort;
        $this->connectPort = $connectPort;
    }

    /**
     * @param DNodeRemoteProxy $remote
     * @return RemoteProxy
     */
    public function createRemoteProxy(DNodeRemoteProxy $remote = null)
    {
        if (!$remote) {
            $remote = new DNodeRemoteProxy();
            $remoteService = $this;
            $remote->setMethod('dispatch', function() use ($remoteService) {
                $args = func_get_args();
                $remoteService->connect(function($remote, $connection) use ($args) {
                    call_user_func_array(array($remote, 'dispatch'), $args);
                    $connection->end();
                });
            });
        }

        return new RemoteProxy($remote);
    }

    /**
     * @param EventLoop\LoopInterface $loop
     */
    private function runListenLoop(EventLoop\LoopInterface $loop)
    {
        try {
            $loop->run();
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            $this->runListenLoop($loop);
        }
    }

    /**
     * @param callable $cb
     */
    public function listen(\Closure $cb)
    {
        $loop = EventLoop\Factory::create();
        $this->service->loop =& $loop;
        $server = new DNode($loop, $this->service);
        $server->listen($this->listenPort, $cb);

        $this->runListenLoop($loop);
    }

    /**
     * @param callable $cb
     */
    public function connect(\Closure $cb)
    {
        $loop = EventLoop\Factory::create();
        $dnode = new DNode($loop);
        $dnode->on('error', function($e) {
            throw $e;
        });
        $dnode->connect($this->connectPort, $cb);

        $loop->run();
    }

} 