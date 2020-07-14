<?php

declare(strict_types=1);

namespace App\Event\Manageable;

use App\Contract\Request\Manageable\ManageableProductRequest;

class ManageableExperienceComponentEvent extends ManageableProductRequest
{
    public static function fromExperienceComponent(
        string $experienceGoldenId,
        string $componentGoldenId
    ): self {
        $manageableProductRequest = new self();
        $manageableProductRequest->experienceGoldenId = $experienceGoldenId;
        $manageableProductRequest->componentGoldenId = $componentGoldenId;

        return $manageableProductRequest;
    }
}
