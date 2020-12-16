<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\EAI;

use App\Contract\Request\EAI\RoomRequest;
use App\Entity\Component;
use App\Entity\Partner;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Contract\Request\EAI\RoomRequest
 */
class RoomRequestTest extends ProphecyTestCase
{
    /**
     * @dataProvider transformFromComponentProvider
     * @covers ::transformFromComponent
     * @covers ::getContext
     */
    public function testTransformFromComponent(
        Component $component,
        array $context,
        callable $asserts = null
    ): void {
        $result = RoomRequest::transformFromComponent($component);
        $asserts($this, $result, $context);
    }

    public function transformFromComponentProvider(): \Generator
    {
        $context = [
            'product_id' => '12345',
            'product_name' => 'abc',
            'product_is_sellable' => true,
            'partner_id' => '54321',
            'component_is_manageable' => true,
            'component_description' => 'test desc',
            'component_room_stock_type' => '2',
        ];

        $component = new Component();
        $component->goldenId = $context['product_id'];
        $component->isSellable = $context['product_is_sellable'];
        $component->name = $context['product_name'];
        $component->partner = new Partner();
        $component->partner->goldenId = $context['partner_id'];
        $component->isManageable = $context['component_is_manageable'];
        $component->description = $context['component_description'];
        $component->roomStockType = $context['component_room_stock_type'];
        $component->currency = 'USD';

        yield 'happy-component' => [
            $component,
            $context,
            (function (RoomRequestTest $test, RoomRequest $result, array $context) {
                $test->assertEquals($result->getProduct()->getName(), $context['product_name']);
                $test->assertEquals($result->getProduct()->getId(), $context['product_id']);
                $test->assertEquals($result->getProduct()->getIsSellable(), $context['product_is_sellable']);
                $test->assertEquals($result->getProduct()->getPartner()->getId(), $context['partner_id']);
                $test->assertEquals($result->getIsActive(), $context['component_is_manageable']);
                $test->assertEquals($result->getProduct()->getDescription(), $context['component_description']);
                $test->assertEquals($result->getProduct()->getRoomStockType(), $context['component_room_stock_type']);
                $test->assertEquals($result->getContext(), $context);
            }),
        ];

        yield 'happy-component-with-price' => [
            (function ($component) {
                $component = clone $component;
                $component->price = 300;
                $component->duration = 3;

                return $component;
            })($component),
            $context,
            (function (RoomRequestTest $test, RoomRequest $result, array $context) {
                $test->assertEquals($result->getProduct()->getName(), $context['product_name']);
                $test->assertEquals($result->getProduct()->getId(), $context['product_id']);
                $test->assertEquals($result->getProduct()->getIsSellable(), $context['product_is_sellable']);
                $test->assertEquals($result->getProduct()->getPartner()->getId(), $context['partner_id']);
                $test->assertEquals($result->getIsActive(), $context['component_is_manageable']);
                $test->assertEquals($result->getProduct()->getDescription(), $context['component_description']);
                $test->assertEquals($result->getProduct()->getRoomStockType(), $context['component_room_stock_type']);
                $test->assertEquals($result->getProduct()->getListPrice()->getAmount(), 10000);
                $test->assertEquals($result->getContext(), $context);
            }),
        ];

        yield 'happy-component-with-price-not-even' => [
            (function ($component) {
                $component = clone $component;
                $component->price = 301;
                $component->duration = 3;

                return $component;
            })($component),
            $context,
            (function (RoomRequestTest $test, RoomRequest $result, array $context) {
                $test->assertEquals($result->getProduct()->getName(), $context['product_name']);
                $test->assertEquals($result->getProduct()->getId(), $context['product_id']);
                $test->assertEquals($result->getProduct()->getIsSellable(), $context['product_is_sellable']);
                $test->assertEquals($result->getProduct()->getPartner()->getId(), $context['partner_id']);
                $test->assertEquals($result->getIsActive(), $context['component_is_manageable']);
                $test->assertEquals($result->getProduct()->getDescription(), $context['component_description']);
                $test->assertEquals($result->getProduct()->getRoomStockType(), $context['component_room_stock_type']);
                $test->assertEquals($result->getProduct()->getListPrice()->getAmount(), 10100);
                $test->assertEquals($result->getContext(), $context);
            }),
        ];
    }

    public function testGetEventName(): void
    {
        $this->assertEquals('Push Rooms to EAI', (new RoomRequest())->getEventName());
    }
}
