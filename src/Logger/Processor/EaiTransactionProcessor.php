<?php

declare(strict_types=1);

namespace App\Logger\Processor;

use App\Helper\EaiTransactionId;

class EaiTransactionProcessor
{
    protected EaiTransactionId $eaiTransactionId;

    public function __construct(EaiTransactionId $eaiTransactionId)
    {
        $this->eaiTransactionId = $eaiTransactionId;
    }

    public function __invoke(array $record): array
    {
        if (isset($record['context'][EaiTransactionId::LOG_FIELD])) {
            $record['extra'][EaiTransactionId::LOG_FIELD] = $record['context'][EaiTransactionId::LOG_FIELD];
        } elseif (null !== $this->eaiTransactionId->getTransactionId()) {
            $record['extra'][EaiTransactionId::LOG_FIELD] = $this->eaiTransactionId->getTransactionId();
            $record['context'][EaiTransactionId::LOG_FIELD] = $this->eaiTransactionId->getTransactionId();
        }

        return $record;
    }
}
