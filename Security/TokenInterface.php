<?php

namespace Cravler\RemoteBundle\Security;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface TokenInterface
{
    public function getUser();

    public function getRemoteKey();
} 