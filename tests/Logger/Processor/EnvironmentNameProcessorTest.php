<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\EnvironmentNameProcessor;
use App\Tests\ProphecyTestCase;

class EnvironmentNameProcessorTest extends ProphecyTestCase
{
    public function testInvoke()
    {
        $environment = 'abcd';
        $processor = new EnvironmentNameProcessor($environment);
        $actual = $processor([]);

        $this->assertEquals($environment, $actual['extra']['environment_name']);
    }
}
