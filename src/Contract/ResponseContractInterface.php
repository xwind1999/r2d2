<?php

declare(strict_types=1);

namespace App\Contract;

interface ResponseContractInterface
{
    public function getHttpCode(): int;
}
