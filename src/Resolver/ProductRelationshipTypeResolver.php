<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Event\ProductRelationship\BoxExperienceRelationshipBroadcastEvent;
use App\Event\ProductRelationship\ExperienceComponentRelationshipBroadcastEvent;
use App\Event\ProductRelationship\ProductRelationshipEventInterface;
use App\Exception\Resolver\UnprocessableProductRelationshipTypeException;

class ProductRelationshipTypeResolver
{
    protected const EXPERIENCE_COMPONENT_TYPE = 'EXPERIENCE-COMPONENT';
    protected const BOX_EXPERIENCE_TYPE = 'BOX-EXPERIENCE';

    /**
     * @throws UnprocessableProductRelationshipTypeException
     */
    public function resolve(ProductRelationshipRequest $relationshipRequest): ProductRelationshipEventInterface
    {
        $relationshipType = strtoupper($relationshipRequest->relationshipType);

        if (self::EXPERIENCE_COMPONENT_TYPE === $relationshipType) {
            return new ExperienceComponentRelationshipBroadcastEvent($relationshipRequest);
        }

        if (self::BOX_EXPERIENCE_TYPE === $relationshipType) {
            return new BoxExperienceRelationshipBroadcastEvent($relationshipRequest);
        }

        throw new UnprocessableProductRelationshipTypeException();
    }
}
