<?php

declare(strict_types=1);

namespace App\Logger\Processor;

use App\Http\CorrelationId\CorrelationId;

class CorrelationIdProcessor
{
    protected CorrelationId $correlationId;

    public function __construct(CorrelationId $correlationId)
    {
        $this->correlationId = $correlationId;
    }

    public function __invoke(array $record): array
    {
        if (isset($record['context'][CorrelationId::LOG_FIELD])) {
            $record['extra'][CorrelationId::LOG_FIELD] = $record['context'][CorrelationId::LOG_FIELD];
        } else {
            $record['extra'][CorrelationId::LOG_FIELD] = $this->correlationId->getCorrelationId();
            $record['context'][CorrelationId::LOG_FIELD] = $this->correlationId->getCorrelationId();
        }

        return $record;
    }
}
