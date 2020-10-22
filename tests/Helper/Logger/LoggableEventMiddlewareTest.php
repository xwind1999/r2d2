<?php

declare(strict_types=1);

namespace App\Tests\Helper\Logger;

use App\Helper\LoggableEventInterface;
use App\Helper\Logger\LoggableEventMiddleware;
use App\Tests\ProphecyTestCase;

class LoggableEventMiddlewareTest extends ProphecyTestCase
{
    public function testProcess()
    {
        $middleware = new LoggableEventMiddleware();
        $message = 'test message';
        $context = ['any-context' => 'any-value'];
        $objMock = $this->prophesize(LoggableEventInterface::class);
        $objMock->getMessage()->willReturn($message);
        $objMock->getContext()->willReturn([]);
        $result = $middleware->process('log', $objMock->reveal(), $context);
        $this->assertEquals([$message, $context], $result);
    }
}
