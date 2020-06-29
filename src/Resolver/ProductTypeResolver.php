<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Constraint\ProductTypeConstraint;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\Event\Product\ComponentBroadcastEvent;
use App\Event\Product\Contract\ProductRequestEventInterface;
use App\Event\Product\ExperienceBroadcastEvent;
use App\Exception\Resolver\UnprocessableProductTypeException;

class ProductTypeResolver
{
    /**
     * @throws UnprocessableProductTypeException
     */
    public function resolve(ProductRequest $productRequest): ProductRequestEventInterface
    {
        $productType = strtoupper($productRequest->type);
        if (ProductTypeConstraint::isValid($productType)) {
            return new BoxBroadcastEvent($productRequest);
        }

        if (ProductTypeConstraint::EXPERIENCE === $productType) {
            return new ExperienceBroadcastEvent($productRequest);
        }

        if (ProductTypeConstraint::COMPONENT === $productType) {
            return new ComponentBroadcastEvent($productRequest);
        }

        throw new UnprocessableProductTypeException();
    }
}
