<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\AbstractLoggableEvent;

abstract class AbstractRelationshipBroadcastEvent extends AbstractLoggableEvent implements ProductRelationshipEventInterface
{
    public const EVENT_NAME = 'product-relationship';

    protected const LOG_MESSAGE = 'Product Relationship Broadcast Event';

    protected const LOG_LEVEL = 'info';

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
        return static::EVENT_NAME;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return $this->relationshipRequest->getContext();
    }
}
