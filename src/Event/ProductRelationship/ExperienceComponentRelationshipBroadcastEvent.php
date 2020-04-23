<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use Symfony\Contracts\EventDispatcher\Event;

class ExperienceComponentRelationshipBroadcastEvent extends Event implements ProductRelationshipEventInterface
{
    public const EVENT_NAME = 'product-relationship.experience-component';

    private ProductRelationshipRequest $relationshipRequest;

    public function __construct(ProductRelationshipRequest $relationshipRequest)
    {
        $this->relationshipRequest = $relationshipRequest;
    }

    public function getProductRelationshipRequest(): ProductRelationshipRequest
    {
        return $this->relationshipRequest;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
