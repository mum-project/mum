<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntegrationParameter extends Model
{
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
     * Gets the integration that this parameter belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'id');
    }

    public function getParameterString()
    {
        $delimiter = $this->use_equal_sign ? '=' : ' ';
        $option = $this->option ? $this->option . $delimiter : '';
        return $option . '\'' . $this->value . '\'';
    }
}
