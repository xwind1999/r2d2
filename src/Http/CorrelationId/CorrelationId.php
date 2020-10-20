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

    private ?string $correlationId = null;

    private ?string $correlationIdOverride = null;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getCorrelationId(): string
    {
        if (null !== $this->correlationIdOverride) {
            return $this->correlationIdOverride;
        }

        if (null === $this->correlationId) {
            return $this->regenerate();
        }

        return $this->correlationId;
    }

    public function setCorrelationIdOverride(string $correlationIdOverride): void
    {
        $this->correlationIdOverride = $correlationIdOverride;
    }

    public function resetCorrelationIdOverride(): void
    {
        $this->correlationIdOverride = null;
    }

    public function regenerate(): string
    {
        $request = $this->requestStack->getMasterRequest() instanceof Request ? $this->requestStack->getMasterRequest() : null;
        $requestCorrelationId = $request ? $request->headers->get(self::HEADER_KEY, null) : null;

        return $this->correlationId = $requestCorrelationId ?? Uuid::uuid4()->toString();
    }
}
