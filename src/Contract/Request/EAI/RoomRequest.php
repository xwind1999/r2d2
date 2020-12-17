<?php

declare(strict_types=1);

namespace App\Contract\Request\EAI;

use App\Entity\Component;
use App\Event\NamedEventInterface;
use App\Helper\MoneyHelper;
use Smartbox\CDM\Entity\Common\Price;
use Smartbox\CDM\Entity\Partner\Partner;
use Smartbox\CDM\Entity\Product\Product;
use Smartbox\CDM\Entity\Product\RoomTypeProduct;

class RoomRequest extends RoomTypeProduct implements NamedEventInterface
{
    private const EVENT_NAME = 'Push Rooms to EAI';

    public static function transformFromComponent(Component $component): self
    {
        $product = new Product();
        $product->setId($component->goldenId);
        $product->setName($component->name);
        $product->setIsSellable($component->isSellable);

        $partner = new Partner();
        $partner->setId($component->partner->goldenId);
        $product->setPartner($partner);

        if (!empty($component->price) && !empty($component->currency)) {
            $price = new Price();
            $dividedPrice = MoneyHelper::divideToInt(
                $component->price,
                $component->currency,
                $component->duration ?? 1
            );
            $price->setAmount(MoneyHelper::convertToDecimal($dividedPrice, $component->currency));
            $price->setCurrencyCode($component->currency);
            $product->setListPrice($price);
        }

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
        $listPrice = $this->getProduct()->getListPrice();

        return [
            'product_id' => $this->getProduct()->getId(),
            'product_name' => $this->getProduct()->getName(),
            'product_is_sellable' => $this->getProduct()->getIsSellable(),
            'partner_id' => $this->getProduct()->getPartner()->getId(),
            'component_is_manageable' => $this->getIsActive(),
            'component_description' => $this->getProduct()->getDescription() ?? '',
            'component_room_stock_type' => $this->getProduct()->getRoomStockType() ?? '',
            'product_price' => $listPrice ? $listPrice->getAmount() : 0.0, // @phpstan-ignore-line
            'product_currency_code' => $listPrice ? $listPrice->getCurrencyCode() : '', // @phpstan-ignore-line
        ];
    }

    public function getEventName(): string
    {
        return static::EVENT_NAME;
    }
}
