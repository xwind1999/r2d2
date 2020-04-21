<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Contract\Response\Room\RoomGetResponse;
use App\Contract\Response\Room\RoomUpdateResponse;
use App\Controller\Api\RoomController;
use App\Entity\Room;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
use App\Manager\RoomManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
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
        $roomManager->get($uuid)->willThrow(RoomNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $roomManager->reveal());
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
        $room->duration = 0;
        $room->isSellable = false;
        $room->isReservable = false;
        $room->status = 'ok';
        $room->createdAt = new \DateTime();
        $room->updatedAt = new \DateTime();

        $controller = new RoomController();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->get($uuid)->willReturn($room);
        $return = $controller->get(Uuid::fromString($uuid), $roomManager->reveal());
        $this->assertEquals(RoomGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($room->goldenId, $return->goldenId);
        $this->assertEquals($room->partnerGoldenId, $return->partnerGoldenId);
        $this->assertEquals($room->name, $return->name);
        $this->assertEquals($room->description, $return->description);
        $this->assertEquals($room->inventory, $return->inventory);
        $this->assertEquals($room->duration, $return->voucherExpirationDuration);
        $this->assertEquals($room->isSellable, $return->isSellable);
        $this->assertEquals($room->isReservable, $return->isReservable);
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
        $roomManager->update($uuid, $roomUpdateRequest)->willThrow(RoomNotFoundException::class);
        $controller = new RoomController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $roomUpdateRequest, $roomManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleRoom
     */
    public function testPut(string $uuid, Room $room)
    {
        $roomUpdateRequest = new RoomUpdateRequest();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->update($uuid, $roomUpdateRequest)->shouldBeCalled()->willReturn($room);
        $controller = new RoomController();
        $response = $controller->put(Uuid::fromString($uuid), $roomUpdateRequest, $roomManager->reveal());
        $this->assertInstanceOf(RoomUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->delete($uuid)->willThrow(RoomNotFoundException::class);
        $controller = new RoomController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $roomManager->reveal());
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
        $response = $controller->delete(Uuid::fromString($uuid), $roomManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Room\RoomCreateResponse::__construct
     * @dataProvider sampleRoom
     */
    public function testCreate(string $uuid, Room $room)
    {
        $roomCreateRequest = new RoomCreateRequest();
        $roomManager = $this->prophesize(RoomManager::class);
        $roomManager->create($roomCreateRequest)->willReturn($room);
        $controller = new RoomController();
        $roomCreateResponse = $controller->create($roomCreateRequest, $roomManager->reveal());

        $this->assertEquals($uuid, $roomCreateResponse->uuid);
    }

    public function sampleRoom(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $room = new Room();
        $room->uuid = $uuidInterface->reveal();
        $room->goldenId = '1234';
        $room->partnerGoldenId = '1234';
        $room->name = 'test room';
        $room->description = 'this is a test room';
        $room->inventory = 2;
        $room->duration = 2;
        $room->isSellable = true;
        $room->isReservable = true;
        $room->status = 'enabled';
        $room->createdAt = new \DateTime();
        $room->updatedAt = new \DateTime();

        yield [$uuid, $room];
    }
}
