<?php

namespace App;

use App\Traits\QueryFilterTrait;
use Illuminate\Database\Eloquent\Model;

class SystemService extends Model
{
    use QueryFilterTrait;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * Gets all service health checks that belong to this system service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serviceHealthChecks()
    {
        return $this->hasMany(ServiceHealthCheck::class);
    }

    /**
     * Gets the latest health check for this system service.
     *
     * @return Model|null|object|static
     */
    public function latestServiceHealthCheck()
    {
        return $this->serviceHealthChecks()->latest()->first();
    }

    /**
     * Returns the "pretty" name of the service and falls back to
     * the actual system service name that is used to check for the
     * running state with systemctl.
     *
     * @return mixed
     */
    public function name()
    {
        return $this->name ?? $this->service;
    }
}
