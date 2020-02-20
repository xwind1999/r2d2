<?php

declare(strict_types=1);

namespace App\Contract;

class ResponseContract implements ResponseContractInterface
{
    public const HTTP_CODE = 200;

    public function getHttpCode(): int
    {
        return static::HTTP_CODE;
    }
}
