<?php

namespace Cravler\RemoteBundle\Background;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Process
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var integer
     */
    private $pid;

    /**
     * @param string $command The command to execute
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Runs the command in a background process.
     *
     * @param string $outputFile File to write the output of the process to; defaults to /dev/null
     * @return void
     */
    public function run($outputFile = '/dev/null')
    {
        $this->pid = shell_exec(sprintf(
            '%s > %s 2>&1 & echo $!',
            $this->command,
            $outputFile
        ));
    }

    /**
     * Returns if the process is currently running.
     *
     * @return boolean TRUE if the process is running, FALSE if not.
     */
    public function isRunning()
    {
        try {
            $result = shell_exec(sprintf('ps %d', $this->pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch(Exception $e) {}

        return false;
    }

    /**
     * Returns the ID of the process.
     *
     * @return integer The ID of the process
     */
    public function getPid()
    {
        return $this->pid;
    }
}
