<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\RoomPrice\RoomPriceUpdateRequest;
use App\Contract\Response\RoomPrice\RoomPriceGetResponse;
use App\Controller\Api\RoomPriceController;
use App\Entity\RoomPrice;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomPriceManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\RoomPriceController
 */
class RoomPriceControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\RoomPrice\RoomPriceGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new RoomPriceController();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $roomPriceManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RoomPrice\RoomPriceGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new RoomPriceController();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $roomPriceManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RoomPrice\RoomPriceGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $currentDate = new \DateTime();
        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $roomPrice->roomGoldenId = '1234';
        $roomPrice->rateBandGoldenId = '5678';
        $roomPrice->date = $currentDate;
        $roomPrice->price = 10;
        $roomPrice->createdAt = new \DateTime();
        $roomPrice->updatedAt = new \DateTime();

        $controller = new RoomPriceController();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->get($uuid)->willReturn($roomPrice);
        $return = $controller->get($uuid, $roomPriceManager->reveal());

        $this->assertEquals(RoomPriceGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($roomPrice->roomGoldenId, $return->roomGoldenId);
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
        $roomPriceManager->update($uuid, $roomPriceUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new RoomPriceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $roomPriceUpdateRequest, $roomPriceManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomPriceUpdateRequest = new RoomPriceUpdateRequest();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->update($uuid, $roomPriceUpdateRequest)->shouldBeCalled();
        $controller = new RoomPriceController();
        $response = $controller->put($uuid, $roomPriceUpdateRequest, $roomPriceManager->reveal());

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $controller = new RoomPriceController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $roomPriceManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new RoomPriceController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $roomPriceManager->reveal());
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
        $response = $controller->delete($uuid, $roomPriceManager->reveal());

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\RoomPrice\RoomPriceCreateResponse::__construct
     */
    public function testCreate()
    {
        $roomPriceCreateRequest = new RoomPriceCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $roomPriceManager->create($roomPriceCreateRequest)->willReturn($roomPrice);
        $controller = new RoomPriceController();
        $roomPriceCreateResponse = $controller->create($roomPriceCreateRequest, $roomPriceManager->reveal());

        $this->assertEquals($uuid, $roomPriceCreateResponse->uuid);
    }
}
