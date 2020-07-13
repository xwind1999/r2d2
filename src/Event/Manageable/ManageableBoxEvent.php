<?php

declare(strict_types=1);

namespace App\Event\Manageable;

use App\Contract\Request\Manageable\ManageableProductRequest;

class ManageableBoxEvent extends ManageableProductRequest
{
    public static function fromBox(string $boxGoldenId): self
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->boxGoldenId = $boxGoldenId;

        return $manageableProductRequest;
    }
}
