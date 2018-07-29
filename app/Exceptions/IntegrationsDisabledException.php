<?php

namespace App\Exceptions;

use App\Integration;
use Exception;
use Throwable;

class IntegrationsDisabledException extends Exception
{
    /** @var Integration */
    protected $integration;

    /**
     * IntegrationsDisabledException constructor.
     *
     * @param string         $message
     * @param Integration    $integration
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = "Integrations are disabled.",
        Integration $integration = null,
        int $code = 0,
                                Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->integration = $integration;
    }

    /**
     * @return Integration
     */
    public function getIntegration()
    {
        return $this->integration;
    }
}
