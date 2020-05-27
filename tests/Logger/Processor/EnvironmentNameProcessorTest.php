<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\EnvironmentNameProcessor;
use PHPUnit\Framework\TestCase;

class EnvironmentNameProcessorTest extends TestCase
{
    public function testInvoke()
    {
        $environment = 'abcd';
        $processor = new EnvironmentNameProcessor($environment);
        $actual = $processor([]);

        $this->assertEquals($environment, $actual['extra']['environment_name']);
    }
}
