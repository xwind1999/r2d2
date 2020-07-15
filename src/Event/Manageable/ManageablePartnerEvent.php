<?php

declare(strict_types=1);

namespace App\Event\Manageable;

use App\Contract\Request\Manageable\ManageableProductRequest;

class ManageablePartnerEvent extends ManageableProductRequest
{
    public static function fromPartner(string $partnerGoldenId): self
    {
        $manageableProductRequest = new self();
        $manageableProductRequest->partnerGoldenId = $partnerGoldenId;

        return $manageableProductRequest;
    }
}
