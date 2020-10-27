<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
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
     * Scope a query to only include shell command integrations
     * for a given event type and model class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $modelClass
     * @param string                                $eventType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEvent(Builder $query, string $modelClass, string $eventType)
    {
        return $query->where([
            ['model_class', '=', $modelClass],
            ['event_type', '=', $eventType],
            ['active', '=', true]
        ]);
    }

    /**
     * Gets all parameters of this integration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parameters()
    {
        return $this->hasMany(IntegrationParameter::class, 'integration_id', 'id');
    }
}
