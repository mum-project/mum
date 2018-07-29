<?php

namespace Tests\Feature\Hashing;

use App\Hashing\Sha256Hasher;
use function hash_equals;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class Sha256HasherTest extends TestCase
{
    use WithFaker;

    public function testInfo()
    {
        $options = [
            'rounds' => 5001,
            'salt'   => bin2hex(random_bytes(8))
        ];
        $hash = crypt($this->faker->sentence, '$5$rounds=' . $options['rounds'] . '$' . $options['salt'] . '$');

        $hasher = new Sha256Hasher();
        $info = $hasher->info($hash);

        $this->assertEquals('SHA256', $info['algo']);
        $this->assertEquals('sha256', $info['algoName']);
        $this->assertEquals($options, $info['options']);
    }

    public function testMake()
    {
        $options = [
            'rounds' => 5001,
            'salt'   => bin2hex(random_bytes(8))
        ];
        $password = $this->faker->sentence;
        $expectedHash = crypt($password, '$5$rounds=' . $options['rounds'] . '$' . $options['salt'] . '$');
        $hasher = new Sha256Hasher();
        $hash = $hasher->make($password, $options);
        $this->assertTrue(hash_equals($expectedHash, $hash));
    }

    public function testMakeWithFixedRounds()
    {
        $options = [
            'rounds' => 5001,
            'salt'   => bin2hex(random_bytes(8))
        ];
        $password = $this->faker->sentence;
        $expectedHash = crypt($password, '$5$rounds=' . $options['rounds'] . '$' . $options['salt'] . '$');
        $hasher = new Sha256Hasher(['rounds' => $options['rounds']]);
        $hash = $hasher->make($password, ['salt' => $options['salt']]);
        $this->assertTrue(hash_equals($expectedHash, $hash));
    }

    public function testCheck()
    {
        $options = [
            'rounds' => 5001,
            'salt'   => bin2hex(random_bytes(8))
        ];
        $password = $this->faker->unique()->sentence;
        $hash = crypt($password, '$5$rounds=' . $options['rounds'] . '$' . $options['salt'] . '$');
        $hasher = new Sha256Hasher();
        $this->assertTrue($hasher->check($password, $hash));
        $this->assertFalse($hasher->check($this->faker->unique()->sentence, $hash));
    }

    public function testCheckEmptyHash()
    {
        $hasher = new Sha256Hasher();
        $this->assertFalse($hasher->check($this->faker->sentence, ''));
    }

    public function testNeedsRehash()
    {
        $oldOptions = ['rounds' => 5000];
        $options = ['rounds' => 5001];
        $password = $this->faker->unique()->sentence;
        $hash = crypt($password, '$5$rounds=' . $oldOptions['rounds'] . '$' . bin2hex(random_bytes(8)) . '$');
        $sha512hash = crypt($password, '$6$rounds=' . $oldOptions['rounds'] . '$' . bin2hex(random_bytes(8)) . '$');
        $hasher = new Sha256Hasher($options);
        $this->assertTrue($hasher->needsRehash($hash));
        $this->assertFalse($hasher->needsRehash($hash, $oldOptions));
        $this->assertFalse($hasher->needsRehash($sha512hash));
    }
}
