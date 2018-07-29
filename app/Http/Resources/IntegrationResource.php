<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationResource extends JsonResource
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
            'id'          => $this->id,
            'model_class' => $this->modal_class,
            'event_type'  => $this->event_type,
            'type'        => $this->type,
            'value'       => $this->value,
            'created_at'  => (string)$this->created_at,
            'updated_at'  => (string)$this->updated_at
        ];
    }
}
