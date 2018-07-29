<?php

namespace App\Exceptions;

use InvalidArgumentException;
use Throwable;

class ShellCommandUndefinedException extends InvalidArgumentException
{
    /**
     * ShellCommandUndefinedException constructor.
     *
     * @param string         $commandId
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $commandId, int $code = 0, Throwable $previous = null)
    {
        parent::__construct('The shell command "' . $commandId . '" is undefined.', $code, $previous);
    }
}
