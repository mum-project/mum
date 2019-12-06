<?php

namespace Tests\Unit;

use App\Process;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessTest extends TestCase
{
    public function testRun()
    {
        $process = new Process();
        $symphonyProcess = $process->run('date');
        $this->assertTrue($symphonyProcess instanceof \Symfony\Component\Process\Process);
    }
}
