<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Contract\Response\Room\RoomGetResponse;
use App\Controller\Api\RoomController;
use App\Entity\Room;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\RoomController
 */
class RoomControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Room\RoomGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new RoomController();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $roomManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Room\RoomGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new RoomController();
        $roomManager = $this->prophesize(RoomManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $roomManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Room\RoomGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $room = new Room();
        $room->uuid = $uuidInterface->reveal();
        $room->goldenId = '5678';
        $room->partnerGoldenId = '5678';
        $room->name = 'room with small bed';
        $room->description = 'the bed is very small';
        $room->inventory = 1;
        $room->isSellable = false;
        $room->status = 'ok';
        $room->createdAt = new \DateTime();
        $room->updatedAt = new \DateTime();

        $controller = new RoomController();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->get($uuid)->willReturn($room);
        $return = $controller->get($uuid, $roomManager->reveal());
        $this->assertEquals(RoomGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($room->goldenId, $return->goldenId);
        $this->assertEquals($room->partnerGoldenId, $return->partnerGoldenId);
        $this->assertEquals($room->name, $return->name);
        $this->assertEquals($room->description, $return->description);
        $this->assertEquals($room->inventory, $return->inventory);
        $this->assertEquals($room->isSellable, $return->isSellable);
        $this->assertEquals($room->status, $return->status);
        $this->assertEquals($room->createdAt, $return->createdAt);
        $this->assertEquals($room->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomUpdateRequest = new RoomUpdateRequest();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->update($uuid, $roomUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new RoomController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $roomUpdateRequest, $roomManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomUpdateRequest = new RoomUpdateRequest();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->update($uuid, $roomUpdateRequest)->shouldBeCalled();
        $controller = new RoomController();
        $response = $controller->put($uuid, $roomUpdateRequest, $roomManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $roomManager = $this->prophesize(RoomManager::class);
        $controller = new RoomController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $roomManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new RoomController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $roomManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->delete($uuid)->shouldBeCalled();
        $controller = new RoomController();
        $response = $controller->delete($uuid, $roomManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Room\RoomCreateResponse::__construct
     */
    public function testCreate()
    {
        $roomCreateRequest = new RoomCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $room = new Room();
        $room->uuid = $uuidInterface->reveal();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->create($roomCreateRequest)->willReturn($room);
        $controller = new RoomController();
        $roomCreateResponse = $controller->create($roomCreateRequest, $roomManager->reveal());

        $this->assertEquals($uuid, $roomCreateResponse->uuid);
    }
}
