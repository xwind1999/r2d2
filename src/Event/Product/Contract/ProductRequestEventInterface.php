<?php

declare(strict_types=1);

namespace App\Event\Product\Contract;

use App\Contract\Request\BroadcastListener\ProductRequest;

interface ProductRequestEventInterface
{
    public function getProductRequest(): ProductRequest;
}
