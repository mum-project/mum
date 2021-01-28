<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use function isUserSuperAdmin;

class MailboxResource extends JsonResource
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
            'id'                      => $this->id,
            'local_part'              => $this->local_part,
            'domain'                  => $this->whenLoaded('domain'),
            'address'                 => $this->resource->address(),
            'name'                    => $this->name,
            'alternative_email'       => $this->alternative_email,
            'quota'                   => $this->quota,
            'homedir'                 => $this->when(isUserSuperAdmin(), function () {
                return $this->homedir;
            }),
            'maildir'                 => $this->when(isUserSuperAdmin(), function () {
                return $this->maildir;
            }),
            'is_super_admin'          => $this->is_super_admin,
            'send_only'               => $this->send_only,
            'active'                  => $this->active,
            'created_at'              => (string)$this->created_at,
            'updated_at'              => (string)$this->updated_at,
            'admins'                  => $this->when(isUserSuperAdmin(), function () {
                return MailboxResource::collection($this->whenLoaded('admins'));
            }),
            'sending_aliases'         => $this->when(isUserSuperAdmin(), function () {
                return MailboxResource::collection($this->whenLoaded('sendingAliases'));
            }),
            'receiving_aliases'       => $this->when(isUserSuperAdmin(), function () {
                return MailboxResource::collection($this->whenLoaded('receivingAliases'));
            }),
            'administrated_domains'   => $this->when(isUserSuperAdmin(), function () {
                return DomainResource::collection($this->whenLoaded('administratedDomains'));
            }),
            'administrated_mailboxes' => $this->when(isUserSuperAdmin(), function () {
                return MailboxResource::collection($this->whenLoaded('administratedMailboxes'));
            })
        ];
    }
}
