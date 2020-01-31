<?php

declare(strict_types=1);

namespace App\Logger\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

class EaiTransactionProcessor
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function addInfo(array $record): array
    {
        $request = $this->requestStack->getMasterRequest();

        if (!$request
            || null === $request->headers
            || !$transactionId = $request->headers->get('x-transaction-id', null)
        ) {
            return $record;
        }

        $record['extra']['eai_transaction_id'] = $transactionId;

        return $record;
    }
}
