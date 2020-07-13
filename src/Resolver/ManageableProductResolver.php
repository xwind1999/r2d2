<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Constraint\ProductTypeConstraint;
use App\Constraint\RelationshipTypeConstraint;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageableBoxEvent;
use App\Event\Manageable\ManageableBoxExperienceEvent;
use App\Event\Manageable\ManageableComponentEvent;
use App\Event\Manageable\ManageableExperienceComponentEvent;
use App\Event\Manageable\ManageableExperienceEvent;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;
use Symfony\Contracts\EventDispatcher\Event;

class ManageableProductResolver
{
    public function resolve(ManageableProductRequest $manageableProductRequest): Event
    {
        $product = $manageableProductRequest->getProductRequest()
            ?: $manageableProductRequest->getProductRelationshipRequest()
        ;

        if ($product instanceof ProductRequest) {
            $productType = strtoupper($product->type);

            if (ProductTypeConstraint::isValid($productType)) {
                return ManageableBoxEvent::fromBox($product->id);
            }

            if (ProductTypeConstraint::COMPONENT === $productType) {
                return ManageableComponentEvent::fromComponent($product->id);
            }

            if (ProductTypeConstraint::EXPERIENCE === $productType) {
                return ManageableExperienceEvent::fromExperience($product->id);
            }
        }

        if ($product instanceof ProductRelationshipRequest) {
            $relationshipType = strtoupper($product->relationshipType);

            if (RelationshipTypeConstraint::EXPERIENCE_COMPONENT === $relationshipType) {
                return ManageableExperienceComponentEvent::fromExperienceComponent(
                    $product->parentProduct,
                    $product->childProduct
                );
            }

            if (RelationshipTypeConstraint::BOX_EXPERIENCE === $relationshipType) {
                return ManageableBoxExperienceEvent::fromBoxExperience($product->parentProduct, $product->childProduct);
            }
        }

        throw new UnprocessableManageableProductTypeException();
    }
}
