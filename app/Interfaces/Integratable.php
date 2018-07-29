<?php

namespace App\Interfaces;

interface Integratable
{
    /**
     * Gets all available placeholders for integrations.
     * Example: ['placeholder' => $model->value]
     * @return array
     */
    public function getIntegratablePlaceholders();

    /**
     * Gets the class name of the integratable.
     *
     * @return string
     */
    public function getIntegratableClassName();
}
