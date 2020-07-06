<?php

declare(strict_types=1);

namespace App\Contract\Request\EAI;

use App\Entity\Component;
use Smartbox\CDM\Entity\Partner\Partner;
use Smartbox\CDM\Entity\Product\Product;
use Smartbox\CDM\Entity\Product\RoomTypeProduct;

class RoomRequest extends RoomTypeProduct
{
    public static function transformFromComponent(Component $component): self
    {
        $product = new Product();
        $product->setId($component->goldenId);
        $product->setName($component->name);
        $product->setIsSellable($component->isSellable);

        $partner = new Partner();
        $partner->setId($component->partner->goldenId);
        $product->setPartner($partner);

        $roomTypeProduct = new self();
        $roomTypeProduct->setIsActive($component->isManageable);
        if (!empty($component->description)) {
            $product->setDescription($component->description);
        }

        if (!empty($component->roomStockType)) {
            $product->setRoomStockType($component->roomStockType);
        }

        $roomTypeProduct->setProduct($product);

        return $roomTypeProduct;
    }

    public function getContext(): array
    {
        return [
            'product_id' => $this->getProduct()->getId(),
            'product_name' => $this->getProduct()->getName(),
            'product_is_sellable' => $this->getProduct()->getIsSellable(),
            'partner_id' => $this->getProduct()->getPartner()->getId(),
            'component_is_active' => $this->getIsActive(),
        ];
    }
}
