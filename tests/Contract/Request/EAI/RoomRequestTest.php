<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\EAI;

use App\Contract\Request\EAI\RoomRequest;
use App\Entity\Component;
use App\Entity\Partner;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Request\EAI\RoomRequest
 */
class RoomRequestTest extends TestCase
{
    /**
     * @covers ::transformFromComponent
     * @covers ::getContext
     */
    public function testTransformFromComponentSuccessfully(): void
    {
        $component = $this->prophesize(Component::class);
        $component->goldenId = '12345';
        $component->isSellable = true;
        $component->name = 'abc';
        $component->partner = ($this->prophesize(Partner::class))->reveal();
        $component->partner->goldenId = '54321';
        $component->isManageable = true;
        $component->description = 'axzwe aert';
        $component->roomStockType = '2';
        $result = RoomRequest::transformFromComponent($component->reveal());
        $expectedContext = [
            'product_id' => 12345,
            'product_name' => 'abc',
            'product_is_sellable' => true,
            'partner_id' => '54321',
            'component_is_manageable' => true,
            'component_description' => 'axzwe aert',
            'component_room_stock_type' => '2',
        ];

        $this->assertEquals($result->getProduct()->getName(), $component->name);
        $this->assertEquals($result->getProduct()->getId(), $component->goldenId);
        $this->assertEquals($result->getProduct()->getIsSellable(), $component->isSellable);
        $this->assertEquals($result->getProduct()->getPartner()->getId(), $component->partner->goldenId);
        $this->assertEquals($result->getIsActive(), $component->isManageable);
        $this->assertEquals($result->getProduct()->getDescription(), $component->description);
        $this->assertEquals($result->getProduct()->getRoomStockType(), $component->roomStockType);
        $this->assertEquals($result->getContext(), $expectedContext);
    }

    public function testGetEventName(): void
    {
        $this->assertEquals('Push Rooms to EAI', (new RoomRequest())->getEventName());
    }
}
