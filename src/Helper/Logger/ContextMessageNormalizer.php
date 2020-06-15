<?php

declare(strict_types=1);

namespace App\Helper\Logger;

use Clogger\MiddlewareInterface;

class ContextMessageNormalizer implements MiddlewareInterface
{
    /**
     * @param mixed $level
     * @param mixed $message
     */
    public function process($level, $message, array $context): array
    {
        if (!empty($context['message']) && !is_string($context['message'])) {
            $context['message_parsed'] = $context['message'];
            unset($context['message']);
        }

        return [$message, $context];
    }
}
