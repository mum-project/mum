<?php

namespace Tests\Unit\Managers;

use App\Hashing\Sha256Hasher;
use App\Hashing\Sha512Hasher;
use Tests\TestCase;

class HashManagerTest extends TestCase
{
    public function testCreateSha256Driver()
    {
        $manager = app('hash');
        $this->assertTrue($manager->createSha256Driver() instanceof Sha256Hasher);
    }

    public function testCreateSha512Driver()
    {
        $manager = app('hash');
        $this->assertTrue($manager->createSha512Driver() instanceof Sha512Hasher);
    }
}
