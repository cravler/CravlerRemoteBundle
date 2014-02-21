<?php

namespace Cravler\RemoteBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class TokenFactory
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var UserProviderInterface
     */
    private $provider;

    /**
     * @param string $secret
     * @param UserProviderInterface $provider
     */
    public function __construct($secret, UserProviderInterface $provider = null)
    {
        $this->secret = $secret;
        $this->provider = $provider;
    }

    /**
     * @param array $token
     * @return Token
     */
    public function createToken(array $token)
    {
        $user = null;
        if (isset($token['user']) && $token['user']) {
            $user = $this->getUser($token['user']);
        }

        if (!isset($token['remoteKey'])) {
            $token['remoteKey'] = array();
        }

        if ($this->createArrayToken($user, $token['remoteKey']) == $token) {
            return new Token($user, $token['remoteKey']);
        }

        return new Token;
    }

    /**
     * @param UserInterface $user
     * @param array $remoteKey
     * @return array
     */
    public function createArrayToken(UserInterface $user = null, array $remoteKey = array())
    {
        $token = array(
            'user'      => '',
            'remoteKey' => $remoteKey,
        );

        $salt = $this->secret;
        if ($user) {
            $salt = $user->getSalt();
            $token['user'] = $user->getUsername();
        }

        $json = json_encode($token + array('salt' => $salt));
        $token['hash'] = base_convert(sha1($json), 16, 36);

        return $token;
    }

    /**
     * @param $username*
     * @return null|UserInterface
     */
    private function getUser($username)
    {
        $user = null;
        if ($this->provider && $username) {
            try {
                $user = $this->provider->loadUserByUsername($username);
            } catch (UsernameNotFoundException $e) {
                //
            }
        }

        return $user;
    }
}