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
        $record['context'][CorrelationId::LOG_FIELD] = $this->correlationId->getUuid();
        $record['extra'][CorrelationId::LOG_FIELD] = $this->correlationId->getUuid();

        return $record;
    }
}
