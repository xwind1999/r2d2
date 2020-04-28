<?php

declare(strict_types=1);

namespace App\Logger\Processor;

use Ramsey\Uuid\UuidFactoryInterface;

class RequestIdProcessor
{
    protected string $requestUuid;

    public function __construct(UuidFactoryInterface $uuidFactory)
    {
        $requestUuid = $uuidFactory->uuid4();
        $this->requestUuid = $requestUuid->toString();
    }

    public function __invoke(array $record): array
    {
        $record['extra']['request_id'] = $this->requestUuid;

        return $record;
    }
}
