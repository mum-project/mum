<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SystemServiceResource extends JsonResource
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
            'service'    => $this->name,
            'name'       => $this->resource->name(),
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at
        ];
    }
}
