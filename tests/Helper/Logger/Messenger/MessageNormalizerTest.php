<?php

declare(strict_types=1);

namespace App\Tests\Helper\Logger\Messenger;

use App\Helper\Logger\Messenger\MessageNormalizer;
use PHPUnit\Framework\TestCase;

class MessageNormalizerTest extends TestCase
{
    public function testProcess()
    {
        $originalMessage = 'Error thrown while handling message {class}. Sending for retry #{retryCount} using {delay} ms delay. Error: "{error}"';
        $expectedMessage = 'Error thrown while handling message {class}.';

        $normalizer = new MessageNormalizer();
        [$message, $context] = $normalizer->process('error', $originalMessage, []);

        $this->assertEquals($expectedMessage, $message);
    }

    public function testProcessWithAnotherMessage()
    {
        $originalMessage = 'Error thrown while handling message {class}. Removing from transport after {retryCount} retries. Error: "{error}"';
        $expectedMessage = 'Error thrown while handling message {class}.';

        $normalizer = new MessageNormalizer();
        [$message, $context] = $normalizer->process('error', $originalMessage, []);

        $this->assertEquals($expectedMessage, $message);
    }

    public function testProcessWithWeirdMessage()
    {
        $originalMessage = new class() {
        };

        $normalizer = new MessageNormalizer();
        [$message, $context] = $normalizer->process('error', $originalMessage, []);

        $this->assertSame($originalMessage, $message);
    }
}
