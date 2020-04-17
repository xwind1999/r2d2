<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\RelationshipRequest;

interface ProductRelationshipEventInterface
{
    public function getRelationshipRequest(): RelationshipRequest;

    public function getEventName(): string;
}
