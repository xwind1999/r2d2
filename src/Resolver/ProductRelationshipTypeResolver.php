<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Event\ProductRelationship\ExperienceComponentEvent;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use Symfony\Contracts\EventDispatcher\Event;

class ProductRelationshipTypeResolver
{
    protected const EXPERIENCE_COMPONENT_TYPE = 'EXPERIENCE-COMPONENT';

    /**
     * @throws NonExistentTypeResolverExcepetion
     */
    public function resolve(RelationshipRequest $relationshipRequest): Event
    {
        if (self::EXPERIENCE_COMPONENT_TYPE === strtoupper($relationshipRequest->relationshipType)) {
            return new ExperienceComponentEvent($relationshipRequest);
        }

        throw new NonExistentTypeResolverExcepetion();
    }
}
