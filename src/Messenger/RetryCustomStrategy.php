<?php

declare(strict_types=1);

namespace App\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

class RetryCustomStrategy implements RetryStrategyInterface
{
    private const MAX_RETRIES = 200;
    private const TEN_SECONDS_TIME_DELAY = 10000;
    private const THIRTY_SECONDS_TIME_DELAY = 30000;
    private const SIXTY_SECONDS_TIME_DELAY = 60000;
    private const TEN_MINUTES_TIME_DELAY = 600000;
    private const THIRTY_MINUTES_TIME_DELAY = 1800000;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function isRetryable(Envelope $message, \Throwable $throwable = null): bool
    {
        if ($throwable instanceof HandlerFailedException) {
            $exceptions = $throwable->getNestedExceptions();
            foreach ($exceptions as $exception) {
                if (in_array(UnrecoverableExceptionInterface::class, class_implements($exception), true)) {
                    $this->logger->error($exception->getMessage(), ['context' => $message->getMessage()]);

                    return false;
                }
            }
        }

        return RedeliveryStamp::getRetryCountFromEnvelope($message) < self::MAX_RETRIES;
    }

    public function getWaitingTime(Envelope $message, \Throwable $throwable = null): int
    {
        $retryCount = RedeliveryStamp::getRetryCountFromEnvelope($message);

        switch ($retryCount) {
            case 0: case 1: return self::TEN_SECONDS_TIME_DELAY;
            case 2: return self::THIRTY_SECONDS_TIME_DELAY;
            case 3: case 4: case 5: case 6: return self::SIXTY_SECONDS_TIME_DELAY;
            case 7: case 8: case 9: case 10: return self::TEN_MINUTES_TIME_DELAY;
            default: return self::THIRTY_MINUTES_TIME_DELAY;
        }
    }
}
