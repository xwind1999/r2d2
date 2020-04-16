<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use Symfony\Contracts\EventDispatcher\Event;

class ExperienceComponentRelationshipBroadcastEvent extends Event
{
    private RelationshipRequest $relationshipRequest;

    public function __construct(RelationshipRequest $relationshipRequest)
    {
        $this->relationshipRequest = $relationshipRequest;
    }

    public function getRelationshipRequest(): RelationshipRequest
    {
        return $this->relationshipRequest;
    }
}
