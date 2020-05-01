<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

class BoxExperienceRelationshipBroadcastEvent extends AbstractRelationshipBroadcastEvent
{
    public const EVENT_NAME = 'product-relationship.box-experience';

    protected const LOG_MESSAGE = 'Box-Experience Relationship Broadcast Event';
}
