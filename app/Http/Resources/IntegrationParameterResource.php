<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationParameterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'option'         => $this->option,
            'value'          => $this->value,
            'use_equal_sign' => $this->use_equal_sign
        ];
    }
}
