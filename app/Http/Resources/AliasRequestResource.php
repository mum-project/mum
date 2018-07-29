<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AliasRequestResource extends JsonResource
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
            'mailbox'     => $this->whenLoaded('mailbox'),
            'domain'      => $this->whenLoaded('domain'),
            'address'     => $this->resource->address(),
            'description' => $this->description,
            'status'      => $this->status,
            'created_at'  => (string)$this->created_at,
            'updated_at'  => (string)$this->updated_at,
        ];
    }
}
