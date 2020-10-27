<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use const FILTER_VALIDATE_URL;
use function filter_var;
use UnexpectedValueException;

class WebHookIntegration extends Integration
{
    use HasFactory;

    protected $table = 'integrations';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->where('type', WebHookIntegration::class);
        });
    }

    /**
     * Set the integration's value.
     *
     * @param  string $value
     * @return void
     * @throws UnexpectedValueException
     */
    public function setValueAttribute($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new UnexpectedValueException('Value was not a valid URL.');
        }
        $this->attributes['value'] = $value;
    }

    public function getUrl()
    {
        return $this->value;
    }

    /**
     * @param string $url
     * @return void
     */
    public function setUrl(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UnexpectedValueException('Value was not a valid URL.');
        }
        $this->value = $url;
    }
}
