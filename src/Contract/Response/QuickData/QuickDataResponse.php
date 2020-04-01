<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData;

use App\Contract\ResponseContract;

abstract class QuickDataResponse extends ResponseContract
{
    public const HTTP_CODE = 200;
}
