<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    use WithFaker;

    public function testGetDomainOfEmailAddress()
    {
        $localPart = $this->faker->userName;
        $domain = $this->faker->safeEmailDomain;
        $email = $localPart . '@' . $domain;
        $this->assertEquals($domain, getDomainOfEmailAddress($email));

        // Check weird local parts
        $this->assertEquals('foobar.com', getDomainOfEmailAddress('"foo@bar"@foobar.com'));
        $this->assertEquals('foo.bar.com', getDomainOfEmailAddress('"foo@bar"@foo.bar.com'));
        $this->assertEquals('foo.bar.com', getDomainOfEmailAddress('f.oo+bar@foo.bar.com'));
    }

    public function testGetLocalPartOfEmailAddress()
    {
        $localPart = $this->faker->userName;
        $domain = $this->faker->safeEmailDomain;
        $email = $localPart . '@' . $domain;
        $this->assertEquals($localPart, getLocalPartOfEmailAddress($email));

        // Check weird local parts
        $this->assertEquals('"foo@bar"', getLocalPartOfEmailAddress('"foo@bar"@foobar.com'));
        $this->assertEquals('"foo@bar"', getLocalPartOfEmailAddress('"foo@bar"@foo.bar.com'));
        $this->assertEquals('f.oo+bar', getLocalPartOfEmailAddress('f.oo+bar@foo.bar.com'));
    }
}
