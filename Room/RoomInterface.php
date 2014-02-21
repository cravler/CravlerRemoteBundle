<?php

namespace Cravler\RemoteBundle\Room;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface RoomInterface
{
    /**
     * Technical name of room.
     *
     * @return string
     */
    public function getId();

    /**
     * Checks if room is allowed.
     *
     * @return bool
     */
    public function isAllowed();
} 