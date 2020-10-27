<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use function config;
use UnexpectedValueException;

class ShellCommandIntegration extends Integration
{
    use HasFactory;

    protected $table = 'integrations';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->where('type', ShellCommandIntegration::class);
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
        if (!config('integrations.shell_commands.' . $value)) {
            throw new UnexpectedValueException('Shell command is not defined.');
        }
        $this->attributes['value'] = $value;
    }

    public function setCommandId(string $commandId)
    {
        $this->value = $commandId;
    }

    public function getCommandId()
    {
        return $this->value;
    }
}
