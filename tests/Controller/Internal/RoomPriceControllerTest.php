<?php

declare(strict_types=1);

namespace App\Tests\Controller\Internal;

use App\Contract\Request\Internal\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\Internal\RoomPrice\RoomPriceUpdateRequest;
use App\Contract\Response\Internal\RoomPrice\RoomPriceGetResponse;
use App\Contract\Response\Internal\RoomPrice\RoomPriceUpdateResponse;
use App\Controller\Internal\RoomPriceController;
use App\Entity\RoomPrice;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\RoomPriceNotFoundException;
use App\Manager\RoomPriceManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Internal\RoomPriceController
 */
class RoomPriceControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\RoomPrice\RoomPriceGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new RoomPriceController();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->get($uuid)->willThrow(RoomPriceNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $roomPriceManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\RoomPrice\RoomPriceGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $currentDate = new \DateTime();
        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $roomPrice->componentGoldenId = '1234';
        $roomPrice->rateBandGoldenId = '5678';
        $roomPrice->date = $currentDate;
        $roomPrice->price = 10;
        $roomPrice->createdAt = new \DateTime();
        $roomPrice->updatedAt = new \DateTime();

        $controller = new RoomPriceController();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->get($uuid)->willReturn($roomPrice);
        $return = $controller->get(Uuid::fromString($uuid), $roomPriceManager->reveal());

        $this->assertEquals(RoomPriceGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($roomPrice->componentGoldenId, $return->componentGoldenId);
        $this->assertEquals($roomPrice->rateBandGoldenId, $return->rateBandGoldenId);
        $this->assertEquals($roomPrice->date, $return->date);
        $this->assertEquals($roomPrice->price, $return->price);
        $this->assertEquals($roomPrice->createdAt, $return->createdAt);
        $this->assertEquals($roomPrice->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomPriceUpdateRequest = new RoomPriceUpdateRequest();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->update($uuid, $roomPriceUpdateRequest)->willThrow(RoomPriceNotFoundException::class);
        $controller = new RoomPriceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $roomPriceUpdateRequest, $roomPriceManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleRoomPrice
     */
    public function testPut(string $uuid, RoomPrice $roomPrice)
    {
        $roomPriceUpdateRequest = new RoomPriceUpdateRequest();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->update($uuid, $roomPriceUpdateRequest)->shouldBeCalled()->willReturn($roomPrice);
        $controller = new RoomPriceController();
        $response = $controller->put(Uuid::fromString($uuid), $roomPriceUpdateRequest, $roomPriceManager->reveal());

        $this->assertInstanceOf(RoomPriceUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->delete($uuid)->willThrow(RoomPriceNotFoundException::class);
        $controller = new RoomPriceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $roomPriceManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->delete($uuid)->shouldBeCalled();
        $controller = new RoomPriceController();
        $response = $controller->delete(Uuid::fromString($uuid), $roomPriceManager->reveal());

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\RoomPrice\RoomPriceCreateResponse::__construct
     * @dataProvider sampleRoomPrice
     */
    public function testCreate(string $uuid, RoomPrice $roomPrice)
    {
        $roomPriceCreateRequest = new RoomPriceCreateRequest();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->create($roomPriceCreateRequest)->willReturn($roomPrice);
        $controller = new RoomPriceController();
        $roomPriceCreateResponse = $controller->create($roomPriceCreateRequest, $roomPriceManager->reveal());

        $this->assertEquals($uuid, $roomPriceCreateResponse->uuid);
    }

    public function sampleRoomPrice(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $roomPrice->componentGoldenId = '1234';
        $roomPrice->rateBandGoldenId = '1234';
        $roomPrice->date = new \DateTime();
        $roomPrice->price = 9990;
        $roomPrice->createdAt = new \DateTime();
        $roomPrice->updatedAt = new \DateTime();

        yield [$uuid, $roomPrice];
    }
}
