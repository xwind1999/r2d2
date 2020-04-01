<?php

declare(strict_types=1);

namespace App\Tests\Helper\Logger;

use App\Helper\LoggableEventInterface;
use App\Helper\Logger\LoggableEventMiddleware;
use PHPUnit\Framework\TestCase;

class LoggableEventMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $middleware = new LoggableEventMiddleware();
        $message = 'test message';
        $context = ['any-context' => 'any-value'];
        $objMock = $this->prophesize(LoggableEventInterface::class);
        $objMock->getMessage()->willReturn($message);
        $result = $middleware->process('log', $objMock->reveal(), $context);
        $this->assertEquals([$message, $context], $result);
    }
}
