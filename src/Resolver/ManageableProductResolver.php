<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Constraint\ProductTypeConstraint;
use App\Constraint\RelationshipTypeConstraint;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;

class ManageableProductResolver
{
    public function resolve(ManageableProductRequest $manageableProductRequest): ManageableProductRequest
    {
        $product = $manageableProductRequest->getProductRequest()
            ?: $manageableProductRequest->getProductRelationshipRequest()
        ;

        if ($product instanceof ProductRequest) {
            $productType = strtoupper($product->type);

            if (ProductTypeConstraint::isValid($productType)) {
                return ManageableProductRequest::fromBox($product->id);
            }

            if (ProductTypeConstraint::COMPONENT === $productType) {
                return ManageableProductRequest::fromComponent($product->id);
            }
        }

        if ($product instanceof ProductRelationshipRequest) {
            $relationshipType = strtoupper($product->relationshipType);

            if (RelationshipTypeConstraint::EXPERIENCE_COMPONENT === $relationshipType) {
                return ManageableProductRequest::fromExperienceComponent($product->childProduct, $product->parentProduct);
            }

            if (RelationshipTypeConstraint::BOX_EXPERIENCE === $relationshipType) {
                return ManageableProductRequest::fromBoxExperience($product->parentProduct, $product->childProduct);
            }
        }

        throw new UnprocessableManageableProductTypeException();
    }
}
