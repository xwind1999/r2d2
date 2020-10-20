<?php

declare(strict_types=1);

namespace App\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class CorrelationIdStamp implements StampInterface
{
    public string $correlationId;

    public function __construct(string $correlationId)
    {
        $this->correlationId = $correlationId;
    }
}
