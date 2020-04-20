<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\Event\Product\Contract\ProductRequestEventInterface;
use App\Event\Product\ExperienceBroadcastEvent;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;

class ProductTypeResolver
{
    protected const BOX_TYPE = [
        'MEV', 'DEV', 'MLV',
    ];

    protected const EXPERIENCE_TYPE = 'EXPERIENCE';

    /**
     * @throws NonExistentTypeResolverExcepetion
     */
    public function resolve(ProductRequest $productRequest): ProductRequestEventInterface
    {
        $productType = strtoupper($productRequest->type);
        if (in_array($productType, self::BOX_TYPE)) {
            return new BoxBroadcastEvent($productRequest);
        }

        if (self::EXPERIENCE_TYPE === $productType) {
            return new ExperienceBroadcastEvent($productRequest);
        }

        throw new NonExistentTypeResolverExcepetion();
    }
}
