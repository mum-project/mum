<?php

namespace App\Hashing;

use function bin2hex;
use function crypt;
use const CRYPT_SHA256;
use function hash_equals;
use function preg_match;
use function random_bytes;
use RuntimeException;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class Sha256Hasher implements HasherContract
{
    /**
     * Create a new hasher instance.
     *
     * @param  array $options
     * @return void
     */
    public function __construct(array $options = [])
    {
        //
    }

    /**
     * Get information about the given hashed value.
     *
     * @param  string $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        if (!preg_match('/\$5\$[^\$\s]+\$[^\$\s]+$/', $hashedValue)) {
            return password_get_info($hashedValue);
        }

        $options = [];
        $matches = [];

        if (preg_match('/\$([^\$\s]+)\$[^\$\s]+$/', $hashedValue, $matches)) {
            $options['salt'] = $matches[1];
        }

        return [
            'algo'     => 'SHA256',
            'algoName' => 'sha256',
            'options'  => $options
        ];
    }

    /**
     * Hash the given value.
     *
     * @param  string $value
     * @param  array  $options
     * @return string
     *
     * @throws \RuntimeException
     */
    public function make($value, array $options = [])
    {
        if (CRYPT_SHA256 !== 1) {
            throw new RuntimeException('SHA256 hashing not supported.');
        }

        $hash = crypt($value, '$5$' . $this->salt($options) . '$');

        return $hash;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string $value
     * @param  string $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return hash_equals($hashedValue, $this->make($value, $this->info($hashedValue)['options'] ?? []));
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }

    /**
     * Extract the salt value from the options array.
     *
     * @param  array $options
     * @return int
     */
    protected function salt(array $options = [])
    {
        return $options['salt'] ?? bin2hex(random_bytes(8));
    }
}
