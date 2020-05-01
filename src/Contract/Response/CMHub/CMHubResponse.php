<?php

declare(strict_types=1);

namespace App\Contract\Response\CMHub;

use App\Contract\ResponseContract;

abstract class CMHubResponse extends ResponseContract
{
    public const HTTP_CODE = 200;
}
