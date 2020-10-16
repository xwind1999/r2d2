<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EaiTransactionId
{
    public const LOG_FIELD = 'eai_transaction_id';
    protected const HEADER_KEY = 'x-transaction-id';

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getTransactionId(): ?string
    {
        $request = $this->requestStack->getMasterRequest() instanceof Request ? $this->requestStack->getMasterRequest() : null;

        return $request ? $request->headers->get(self::HEADER_KEY, null) : null;
    }
}
