<?php

namespace Cravler\RemoteBundle\Endpoint;

use Cravler\RemoteBundle\Security\Token;
use Cravler\RemoteBundle\Proxy\RemoteProxy;
use Cravler\RemoteBundle\Room\RoomInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Example
{
    /**
     * @var RoomInterface
     */
    private $room;

    /**
     * @param RoomInterface $room
     */
    public function __construct(RoomInterface $room)
    {
        $this->room = $room;
    }

    /**
     * @param $cb
     */
    public function foo($cb = null)
    {
        if (is_callable($cb)) {
            $cb('anonymous');
        }
    }

    /**
     * @param $cb
     * @param Token $token
     * @param RemoteProxy $remote
     */
    public function bar($cb = null, Token $token, RemoteProxy $remote)
    {
        $response = 'anonymous';

        if ($token->getUser()) {
            $response = 'user: ' . $token->getUser()->getUsername();
        }

        if (is_callable($cb)) {
            $cb($response);
        }

        $remote->dispatch(array(
            'type' => 'cravler_remote.example.bar',
            'name' => 'first',
            'data' => array(
                'response' => $response
            ),
        ));

        $remote->dispatch($this->room->getId(), array(
            'type' => 'cravler_remote.example.bar',
            'name' => 'second',
            'data' => array(
                'only'     => $this->room->getId(),
                'response' => $response,
            ),
        ));
    }
}
