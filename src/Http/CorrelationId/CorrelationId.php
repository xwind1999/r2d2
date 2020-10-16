<?php

declare(strict_types=1);

namespace App\Http\CorrelationId;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CorrelationId
{
    public const LOG_FIELD = 'correlation_id';
    public const HEADER_KEY = 'Correlation-Id';

    private string $uuid;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->uuid = $this->generateUuid();
    }

    protected function generateUuid(): string
    {
        $request = $this->requestStack->getMasterRequest() instanceof Request ? $this->requestStack->getMasterRequest() : null;

        $requestCorrelationId = $request ? $request->headers->get(self::HEADER_KEY) : null;

        return $requestCorrelationId ?? Uuid::uuid4()->toString();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
