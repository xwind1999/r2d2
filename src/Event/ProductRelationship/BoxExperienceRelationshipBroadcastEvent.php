<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use Symfony\Contracts\EventDispatcher\Event;

class BoxExperienceRelationshipBroadcastEvent extends Event implements ProductRelationshipEventInterface
{
    public const EVENT_NAME = 'relationship.box-experience';

    private RelationshipRequest $relationshipRequest;

    public function __construct(RelationshipRequest $relationshipRequest)
    {
        $this->relationshipRequest = $relationshipRequest;
    }

    public function getRelationshipRequest(): RelationshipRequest
    {
        return $this->relationshipRequest;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }
}
