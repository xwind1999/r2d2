<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

class ExperienceComponentRelationshipBroadcastEvent extends AbstractRelationshipBroadcastEvent
{
    public const EVENT_NAME = 'product-relationship.experience-component';

    protected const LOG_MESSAGE = 'Experience-Component Relationship Broadcast Event';
}
