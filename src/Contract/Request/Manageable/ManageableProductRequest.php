<?php

declare(strict_types=1);

namespace App\Contract\Request\Manageable;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;

class ManageableProductRequest
{
    public string $boxGoldenId = '';
    public string $componentGoldenId = '';
    public string $experienceGoldenId = '';
    private ?ProductRequest $productRequest = null;
    private ?ProductRelationshipRequest $productRelationshipRequest = null;

    public static function fromBox(string $boxGoldenId): ManageableProductRequest
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->boxGoldenId = $boxGoldenId;

        return $manageableProductRequest;
    }

    public static function fromComponent(string $componentGoldenId): ManageableProductRequest
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->componentGoldenId = $componentGoldenId;

        return $manageableProductRequest;
    }

    public static function fromExperience(string $experienceGoldenId): ManageableProductRequest
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->experienceGoldenId = $experienceGoldenId;

        return $manageableProductRequest;
    }

    public static function fromExperienceComponent(
        string $componentGoldenId,
        string $experienceGoldenId
    ): ManageableProductRequest {
        $manageableProductRequest = new self();
        $manageableProductRequest->componentGoldenId = $componentGoldenId;
        $manageableProductRequest->experienceGoldenId = $experienceGoldenId;

        return $manageableProductRequest;
    }

    public static function fromBoxExperience(string $boxGoldenId, string $experienceGoldenId): ManageableProductRequest
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->boxGoldenId = $boxGoldenId;
        $manageableProductRequest->experienceGoldenId = $experienceGoldenId;

        return $manageableProductRequest;
    }

    public function setProductRequest(ProductRequest $productRequest): void
    {
        $this->productRequest = $productRequest;
    }

    public function getProductRequest(): ?ProductRequest
    {
        return $this->productRequest;
    }

    public function setProductRelationshipRequest(ProductRelationshipRequest $productRelationshipRequest): void
    {
        $this->productRelationshipRequest = $productRelationshipRequest;
    }

    public function getProductRelationshipRequest(): ?ProductRelationshipRequest
    {
        return $this->productRelationshipRequest;
    }

    public function getContext(): array
    {
        return [
            'box_golden_id' => $this->boxGoldenId,
            'componentGoldenId' => $this->componentGoldenId,
            'experienceGoldenId' => $this->experienceGoldenId,
        ];
    }
}
