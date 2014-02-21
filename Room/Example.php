<?php

namespace Cravler\RemoteBundle\Room;

use Cravler\RemoteBundle\Room\RoomInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Example implements RoomInterface
{
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return 'cravler_remote.room.example';
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowed()
    {
        return true;
    }
}