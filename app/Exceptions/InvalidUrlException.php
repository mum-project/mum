<?php

namespace App\Exceptions;

use InvalidArgumentException;
use Throwable;

class InvalidUrlException extends InvalidArgumentException
{
    /**
     * InvalidUrlException constructor.
     *
     * @param string         $url
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $url, int $code = 0, Throwable $previous = null)
    {
        parent::__construct('The URL "' . $url . '" is invalid.', $code, $previous);
    }
}
