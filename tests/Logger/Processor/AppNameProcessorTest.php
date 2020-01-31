<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\AppNameProcessor;
use PHPUnit\Framework\TestCase;

class AppNameProcessorTest extends TestCase
{
    public function testAddInfo(): void
    {
        $processor = new AppNameProcessor();
        $this->assertEquals(
            ['test', 'test2', 'extra' => ['syslog5424_app' => 'r2-d2']],
            $processor->addInfo(['test', 'test2'])
        );
    }
}
