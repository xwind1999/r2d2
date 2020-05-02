<?php

declare(strict_types=1);

namespace App\Event\ProductRelationship;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use Clogger\ContextualInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractRelationshipBroadcastEvent extends Event implements ProductRelationshipEventInterface, ContextualInterface
{
    protected const LOG_MESSAGE = 'Product Relationship Broadcast Event';

    private ProductRelationshipRequest $relationshipRequest;

    public function __construct(ProductRelationshipRequest $relationshipRequest)
    {
        $this->relationshipRequest = $relationshipRequest;
    }

    public function getProductRelationshipRequest(): ProductRelationshipRequest
    {
        return $this->relationshipRequest;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return $this->relationshipRequest->getContext();
    }
}
