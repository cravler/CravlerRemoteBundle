<?php

namespace Cravler\RemoteBundle\Doctrine\DBAL;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection as DoctrineConnection;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Connection extends DoctrineConnection
{
    /**
     * Ping the server
     *
     * @example
     *
     *   if ($conn->ping() === false) {
     *      $conn->close();
     *      $conn->connect();
     *   }
     *
     * @return bool
     */
    public function ping()
    {
        foreach (class_parents($this) as $parent) {
            if (method_exists($parent, 'ping')) {
                return parent::ping();
            }
        }

        $this->connect();

        try {
            $this->query($this->getDatabasePlatform()->getDummySelectSQL());
            return true;
        } catch (DBALException $e) {
            return false;
        }
    }
}