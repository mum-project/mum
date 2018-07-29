<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ShellCommandFailedException extends Exception
{
    /** @var string */
    protected $shellCommand;

    /** @var string */
    protected $errorOutput;

    /** @var int */
    protected $exitCode;

    /**
     * ShellCommandFailedException constructor.
     *
     * @param string         $shellCommand
     * @param string         $errorOutput
     * @param int            $exitCode
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $shellCommand,
        string $errorOutput,
        int $exitCode,
        int $code = 0,
                                Throwable $previous = null
    ) {
        parent::__construct('The shell command "' . $shellCommand . '" failed.', $code, $previous);
        $this->shellCommand = $shellCommand;
        $this->errorOutput = $errorOutput;
        $this->exitCode = $exitCode;
    }

    /**
     * Gets the failed shell command.
     *
     * @return string
     */
    public function getShellCommand()
    {
        return $this->shellCommand;
    }

    /**
     * @return string
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
