<?php

declare(strict_types=1);

namespace App\Logger\Processor;

use Ramsey\Uuid\UuidFactoryInterface;

class RequestIdProcessor
{
    protected UuidFactoryInterface $uuidFactory;

    protected string $requestUuid;

    public function __construct(UuidFactoryInterface $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
        $this->regenerate();
    }

    public function __invoke(array $record): array
    {
        $record['extra']['request_id'] = $this->requestUuid;

        return $record;
    }

    public function regenerate(): void
    {
        $this->requestUuid = $this->uuidFactory->uuid4()->toString();
    }
}
