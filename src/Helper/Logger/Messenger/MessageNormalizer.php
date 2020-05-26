<?php

declare(strict_types=1);

namespace App\Helper\Logger\Messenger;

use Clogger\MiddlewareInterface;

class MessageNormalizer implements MiddlewareInterface
{
    private const RETRY_MESSAGE_SUBSTRING = 'Sending for retry #{retryCount} using {delay} ms delay. Error: "{error}"';
    private const REMOVING_MESSAGE_SUBSTRING = 'Removing from transport after {retryCount} retries. Error: "{error}"';

    /**
     * @param mixed $level
     * @param mixed $message
     */
    public function process($level, $message, array $context): array
    {
        if (!is_string($message)) {
            return [$message, $context];
        }

        $message = str_replace(self::RETRY_MESSAGE_SUBSTRING, '', $message);
        $message = str_replace(self::REMOVING_MESSAGE_SUBSTRING, '', $message);

        return [trim($message), $context];
    }
}
