<?php

declare(strict_types=1);

namespace App\Event\Manageable;

use App\Contract\Request\Manageable\ManageableProductRequest;

class ManageableComponentEvent extends ManageableProductRequest
{
    public static function fromComponent(string $componentGoldenId): self
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->componentGoldenId = $componentGoldenId;

        return $manageableProductRequest;
    }
}
