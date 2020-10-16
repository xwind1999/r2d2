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
        if ($this->eaiTransactionId->getTransactionId()) {
            $record['extra'][EaiTransactionId::LOG_FIELD] = $this->eaiTransactionId->getTransactionId();
            $record['context'][EaiTransactionId::LOG_FIELD] = $this->eaiTransactionId->getTransactionId();
        }

        return $record;
    }
}
