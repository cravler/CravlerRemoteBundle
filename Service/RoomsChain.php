<?php

namespace Cravler\RemoteBundle\Service;

use Cravler\RemoteBundle\Room\RoomInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class RoomsChain
{
    /**
     * @var array
     */
    private $rooms = array();

    /**
     * @param $room
     */
    public function addRoom($room)
    {
        if ($room instanceof RoomInterface) {
            $this->rooms[$room->getId()] = $room;
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRoom($key)
    {
        return $this->rooms[$key];
    }

    /**
     * @return array
     */
    public function getRooms()
    {
        return $this->rooms;
    }
}
