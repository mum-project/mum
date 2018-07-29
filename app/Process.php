<?php

namespace App;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{
    /**
     * @param      $command
     * @param null $directory
     * @return \Symfony\Component\Process\Process
     */
    public function run($command, $directory = null)
    {
        $directory = $directory ?: base_path();
        $process = new SymfonyProcess($command);
        $process->setWorkingDirectory($directory);
        $process->run();
        return $process;
    }
}
