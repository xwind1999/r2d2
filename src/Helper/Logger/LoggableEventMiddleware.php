<?php

declare(strict_types=1);

namespace App\Helper\Logger;

use App\Helper\LoggableEventInterface;
use Clogger\MiddlewareInterface;

class LoggableEventMiddleware implements MiddlewareInterface
{
    /**
     * @param mixed $level
     * @param mixed $message
     */
    public function process($level, $message, array $context): array
    {
        if ($message instanceof LoggableEventInterface) {
            $message = $message->getMessage();
        }

        return [$message, $context];
    }
}
