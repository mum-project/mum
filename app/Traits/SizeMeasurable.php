<?php

namespace App\Traits;

use App\SizeMeasurement;

trait SizeMeasurable
{
    /**
     * Automatically delete all size measurements if the model get's deleted.
     */
    protected static function bootSizeMeasurable()
    {
        self::deleting(function ($model) {
            $model->sizeMeasurements()
                ->delete();
        });
    }

    /**
     * Gets all size measurements that belong to this model.
     *
     * @param null $amount
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function sizeMeasurements($amount = null)
    {
        $query = $this->morphMany(SizeMeasurement::class, 'measurable');

        if ($amount) {
            $query = $query->orderBy('created_at', 'desc')
                ->limit($amount);
        }

        return $query;
    }
}
