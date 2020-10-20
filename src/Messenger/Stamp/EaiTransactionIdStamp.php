<?php

declare(strict_types=1);

namespace App\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class EaiTransactionIdStamp implements StampInterface
{
    public string $eaiTransactionId;

    public function __construct(string $eaiTransactionId)
    {
        $this->eaiTransactionId = $eaiTransactionId;
    }
}
