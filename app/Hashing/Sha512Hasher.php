<?php

namespace App\Hashing;

use function array_key_exists;
use function bin2hex;
use function crypt;
use const CRYPT_SHA512;
use function hash_equals;
use function is_array;
use const MHASH_SHA512;
use function preg_match;
use function random_bytes;
use RuntimeException;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class Sha512Hasher implements HasherContract
{
    /**
     * The default cost factor.
     *
     * @var int
     */
    protected $rounds = 5000;

    /**
     * Create a new hasher instance.
     *
     * @param  array $options
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->rounds = $options['rounds'] ?? $this->rounds;
    }

    /**
     * Get information about the given hashed value.
     *
     * @param  string $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        if (!preg_match('/\$6\$(rounds=[0-9]+\$)?[^\$\s]+\$[^\$\s]+$/', $hashedValue)) {
            return password_get_info($hashedValue);
        }

        $options = [];
        $matches = [];
        if (preg_match('/rounds=([0-9]+)\$[^\$\s]+\$[^\$\s]+$/', $hashedValue, $matches)) {
            $options['rounds'] = (int)$matches[1];
        }

        if (preg_match('/\$([^\$\s]+)\$[^\$\s]+$/', $hashedValue, $matches)) {
            $options['salt'] = $matches[1];
        }

        return [
            'algo'     => MHASH_SHA512,
            'algoName' => 'sha512',
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
        if (CRYPT_SHA512 !== 1) {
            throw new RuntimeException('SHA512 hashing not supported.');
        }

        $hash = crypt($value, '$6$rounds=' . $this->rounds($options) . '$' . $this->salt($options) . '$');

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
        $info = $this->info($hashedValue);
        if ($info['algo'] === MHASH_SHA512) {
            return $info['options']['rounds'] !== $this->rounds($options);
        }
        return false;
    }

    /**
     * Set the default password work factor.
     *
     * @param  int $rounds
     * @return $this
     */
    public function setRounds($rounds)
    {
        $this->rounds = (int)$rounds;

        return $this;
    }

    /**
     * Extract the rounds value from the options array.
     *
     * @param  array $options
     * @return int
     */
    protected function rounds(array $options = [])
    {
        return $options['rounds'] ?? $this->rounds;
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
