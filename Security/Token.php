<?php

namespace Cravler\RemoteBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Token implements TokenInterface
{
    /**
     * @var UserInterface
     */
    private $user = null;

    /**
     * @var array
     */
    private $remoteKey = array();

    /**
     * @param UserInterface $user
     * @param array $remoteKey
     */
    public function __construct(UserInterface $user = null, array $remoteKey = array())
    {
        $this->user      = $user;
        $this->remoteKey = $remoteKey;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getRemoteKey()
    {
        return $this->remoteKey;
    }
}