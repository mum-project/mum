<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AliasResource extends JsonResource
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
            'local_part'  => $this->local_part,
            'domain'      => $this->whenLoaded('domain'),
            'address'     => $this->resource->address(),
            'active'      => $this->active,
            'description' => $this->description,
            'created_at'  => (string)$this->created_at,
            'updated_at'  => (string)$this->updated_at,
        ];
    }
}
