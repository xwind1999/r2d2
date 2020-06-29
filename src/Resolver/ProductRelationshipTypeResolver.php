<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Constraint\RelationshipTypeConstraint;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\Event\ProductRelationship\ProductRelationshipEventInterface;
use App\Exception\Resolver\UnprocessableProductRelationshipTypeException;

class ProductRelationshipTypeResolver
{
    /**
     * @throws UnprocessableProductRelationshipTypeException
     */
    public function resolve(ProductRelationshipRequest $relationshipRequest): ProductRelationshipEventInterface
    {
        $relationshipType = strtoupper($relationshipRequest->relationshipType);

        if (RelationshipTypeConstraint::EXPERIENCE_COMPONENT === $relationshipType) {
            return new ExperienceComponentRelationshipBroadcastEvent($relationshipRequest);
        }

        if (RelationshipTypeConstraint::BOX_EXPERIENCE === $relationshipType) {
            return new BoxExperienceRelationshipBroadcastEvent($relationshipRequest);
        }

        throw new UnprocessableProductRelationshipTypeException();
    }
}
