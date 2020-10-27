<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHealthCheck extends Model
{
    use HasFactory;

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
     * Gets the system service that this service health check belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function systemService()
    {
        return $this->belongsTo(SystemService::class);
    }

    /**
     * Asserts whether the system service was running at the time
     * of the health check.
     *
     * @return bool
     */
    public function wasRunning()
    {
        return $this->output === 'running' || $this->output === 'exited';
    }

    /**
     * Scope a query to only include states that were running.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereRunning(Builder $query)
    {
        return $query->where(function (Builder $query) {
            $query->where('output', 'running')
                ->orWhere('output', 'exited');
        });
    }

    /**
     * Scope a query to only include states that were running.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereNotRunning(Builder $query)
    {
        return $query->where(function (Builder $query) {
            $query->where('output', '!=', 'running')
                ->where('output', '!=', 'exited');
        });
    }
}
