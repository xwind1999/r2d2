<?php

declare(strict_types=1);

namespace App\Tests\Helper\Logger;

use App\Helper\Logger\ContextMessageNormalizer;
use PHPUnit\Framework\TestCase;

class ContextMessageNormalizerTest extends TestCase
{
    public function testProcessWithContextMessageBeingAnArray(): void
    {
        $middleware = new ContextMessageNormalizer();
        $message = 'test message';
        $context = ['message' => ['aaaa' => 'bbbb']];
        $expectedContext = ['message_parsed' => ['aaaa' => 'bbbb']];
        $result = $middleware->process('log', $message, $context);
        $this->assertEquals([$message, $expectedContext], $result);
    }

    public function testProcessWithContextMessageBeingAClass(): void
    {
        $middleware = new ContextMessageNormalizer();
        $message = 'test message';
        $contextMessage = new class() {
            public string $variable1 = 'aaa';
            public string $variable2 = 'bbb';
            public string $variable3 = 'ccc';
            public string $variable4 = 'ddd';
        };
        $context = ['message' => $contextMessage];
        $expectedContext = ['message_parsed' => $contextMessage];
        $result = $middleware->process('log', $message, $context);
        $this->assertEquals([$message, $expectedContext], $result);
    }
}
