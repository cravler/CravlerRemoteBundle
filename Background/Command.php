<?php

namespace Cravler\RemoteBundle\Background;

use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Command extends Process
{
    /**
     * @param string $command The command to execute
     */
    public function __construct($command)
    {
        $php = escapeshellarg(static::getPhp());
        $console = escapeshellarg('app/console');
        parent::__construct($php . ' ' . $console . ' ' . $command);
    }

    /**
     * @return false|string
     * @throws \RuntimeException
     */
    private static function getPhp()
    {
        $phpFinder = new PhpExecutableFinder;
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }

} 