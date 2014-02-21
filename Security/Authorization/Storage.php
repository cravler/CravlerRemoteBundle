<?php

namespace Cravler\RemoteBundle\Security\Authorization;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Storage
{
    /**
     * @var array
     */
    private $tokens = array();

    /**
     * @param array $token
     */
    public function add(array $token)
    {
        $this->tokens[] = $token;
    }

    /**
     * @param array $token
     */
    public function remove(array $token)
    {
        if(($key = array_search($token, $this->tokens)) !== false) {
            unset($this->tokens[$key]);
        }
    }

    /**
     * @param array $token
     * @return bool
     */
    public function tokenExists(array $token)
    {
        if(in_array($token, $this->tokens)) {
            return true;
        }

        return false;
    }
}