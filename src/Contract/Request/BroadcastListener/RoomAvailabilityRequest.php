<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\Product\Product;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class RoomAvailabilityRequest implements ContextualInterface
{
    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Product")
     * @Assert\Valid
     * @Assert\NotBlank
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Product")
     */
    public Product $product;

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     * @JMS\Type("integer")
     */
    public int $quantity;

    /**
     * @Assert\Type(type="DateTime")
     * @SWG\Property(example="2020-07-16T20:00:00.000000+0000")
     * @Assert\NotBlank
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    public \DateTime $dateFrom;

    /**
     * @Assert\Type(type="DateTime")
     * @SWG\Property(example="2020-07-20T20:00:00.000000+0000")
     * @Assert\NotBlank
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    public \DateTime $dateTo;

    /**
     * @Assert\Type(type="DateTime")
     * @SWG\Property(example="2020-07-20T17:58:32.000000+0000")
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    public ?\DateTime $updatedAt = null;

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return [
            'product' => $this->product->getContext(),
            'quantity' => $this->quantity,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }
}
