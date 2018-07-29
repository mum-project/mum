<?php

namespace App\Managers;

use App\Hashing\Sha256Hasher;
use App\Hashing\Sha512Hasher;
use Illuminate\Hashing\HashManager as CoreHashManager;

class HashManager extends CoreHashManager
{
    /**
     * Create an instance of the SHA-512 hash Driver.
     *
     * @return Sha512Hasher
     */
    public function createSha512Driver()
    {
        return new Sha512Hasher($this->app['config']['hashing.sha512'] ?? []);
    }

    /**
     * Create an instance of the SHA-256 hash Driver.
     *
     * @return Sha256Hasher
     */
    public function createSha256Driver()
    {
        return new Sha256Hasher($this->app['config']['hashing.sha256'] ?? []);
    }
}
