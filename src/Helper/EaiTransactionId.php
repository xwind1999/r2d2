<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EaiTransactionId
{
    public const LOG_FIELD = 'eai_transaction_id';
    protected const HEADER_KEY = 'x-transaction-id';

    private ?string $transactionId = null;
    private ?string $transactionIdOverride = null;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function loadTransactionId(): void
    {
        $request = $this->requestStack->getMasterRequest() instanceof Request ? $this->requestStack->getMasterRequest() : null;
        $this->transactionId = $request ? $request->headers->get(self::HEADER_KEY, null) : null;
    }

    public function getTransactionId(): ?string
    {
        $this->loadTransactionId();

        if (null !== $this->transactionIdOverride) {
            return $this->transactionIdOverride;
        }

        return $this->transactionId;
    }

    public function setTransactionIdOverride(string $transactionId): void
    {
        $this->transactionIdOverride = $transactionId;
    }

    public function resetTransactionIdOverride(): void
    {
        $this->transactionIdOverride = null;
    }
}
