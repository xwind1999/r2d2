<?php

declare(strict_types=1);

namespace App\Event\Manageable;

use App\Contract\Request\Manageable\ManageableProductRequest;

class ManageableExperienceEvent extends ManageableProductRequest
{
    public static function fromExperience(string $experienceGoldenId): self
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->experienceGoldenId = $experienceGoldenId;

        return $manageableProductRequest;
    }
}
