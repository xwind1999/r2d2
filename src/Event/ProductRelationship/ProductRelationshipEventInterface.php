<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;

interface ProductRelationshipEventInterface
{
    public function getProductRelationshipRequest(): ProductRelationshipRequest;

    public function getEventName(): string;
}
