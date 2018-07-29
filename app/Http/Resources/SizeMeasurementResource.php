<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeMeasurementResource extends JsonResource
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
            'id'         => $this->id,
            'size'       => $this->size,
            'created_at' => (string)$this->created_at
        ];
    }
}
