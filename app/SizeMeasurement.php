<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeMeasurement extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Gets the size measurable models that this size measurement belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function Measurable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include size measurements that do not
     * belong to a specific model but the whole application.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param null                                  $amount
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfRootFolder(Builder $query, $amount = null)
    {
        $query = $query->whereNull('measurable_id')
            ->whereNull('measurable_type')
            ->orderBy('created_at', 'desc');

        if ($amount) {
            return $query->limit($amount);
        }

        return $query;
    }
}
