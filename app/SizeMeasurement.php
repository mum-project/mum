<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Gets the size measurable models that this size measurement belongs to.
     *
     * @return MorphTo
     */
    public function Measurable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include size measurements that do not
     * belong to a specific model but the whole application.
     *
     * @param Builder $query
     * @param null $amount
     * @return Builder
     */
    public function scopeOfRootFolder(Builder $query, $amount = null): Builder
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
