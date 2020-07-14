<?php

declare(strict_types=1);

namespace App\Event\Manageable;

use App\Contract\Request\Manageable\ManageableProductRequest;

class ManageableBoxExperienceEvent extends ManageableProductRequest
{
    public static function fromBoxExperience(
        string $boxGoldenId,
        string $experienceGoldenId
    ): self {
        $manageableProductRequest = new self();
        $manageableProductRequest->boxGoldenId = $boxGoldenId;
        $manageableProductRequest->experienceGoldenId = $experienceGoldenId;

        return $manageableProductRequest;
    }
}
