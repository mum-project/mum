<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
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
            'id'            => $this->id,
            'domain'        => $this->domain,
            'description'   => $this->description,
            'quota'         => $this->quota,
            'max_quota'     => $this->max_quota,
            'max_aliases'   => $this->max_aliases,
            'max_mailboxes' => $this->max_mailboxes,
            'active'        => $this->active,
            'created_at'    => (string)$this->created_at,
            'updated_at'    => (string)$this->updated_at
        ];
    }
}
