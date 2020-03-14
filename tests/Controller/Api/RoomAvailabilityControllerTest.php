<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Contract\Response\RoomAvailability\RoomAvailabilityGetResponse;
use App\Contract\Response\RoomAvailability\RoomAvailabilityUpdateResponse;
use App\Controller\Api\RoomAvailabilityController;
use App\Entity\RoomAvailability;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\RoomAvailabilityNotFoundException;
use App\Manager\RoomAvailabilityManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\RoomAvailabilityController
 */
class RoomAvailabilityControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\RoomAvailability\RoomAvailabilityGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new RoomAvailabilityController();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->get($uuid)->willThrow(RoomAvailabilityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $roomAvailabilityManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RoomAvailability\RoomAvailabilityGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $roomAvailability->roomGoldenId = '1234';
        $roomAvailability->rateBandGoldenId = '5678';
        $roomAvailability->stock = 2;
        $roomAvailability->date = new \DateTime();
        $roomAvailability->type = 'instant';
        $roomAvailability->createdAt = new \DateTime();
        $roomAvailability->updatedAt = new \DateTime();

        $controller = new RoomAvailabilityController();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->get($uuid)->willReturn($roomAvailability);
        $return = $controller->get(Uuid::fromString($uuid), $roomAvailabilityManager->reveal());
        $this->assertEquals(RoomAvailabilityGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($roomAvailability->roomGoldenId, $return->roomGoldenId);
        $this->assertEquals($roomAvailability->rateBandGoldenId, $return->rateBandGoldenId);
        $this->assertEquals($roomAvailability->stock, $return->stock);
        $this->assertEquals($roomAvailability->date, $return->date);
        $this->assertEquals($roomAvailability->type, $return->type);
        $this->assertEquals($roomAvailability->createdAt, $return->createdAt);
        $this->assertEquals($roomAvailability->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityUpdateRequest = new RoomAvailabilityUpdateRequest();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->update($uuid, $roomAvailabilityUpdateRequest)->willThrow(RoomAvailabilityNotFoundException::class);
        $controller = new RoomAvailabilityController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $roomAvailabilityUpdateRequest, $roomAvailabilityManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleRoomAvailability
     */
    public function testPut(string $uuid, RoomAvailability $roomAvailability)
    {
        $roomAvailabilityUpdateRequest = new RoomAvailabilityUpdateRequest();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->update($uuid, $roomAvailabilityUpdateRequest)->shouldBeCalled()->willReturn($roomAvailability);
        $controller = new RoomAvailabilityController();
        $response = $controller->put(Uuid::fromString($uuid), $roomAvailabilityUpdateRequest, $roomAvailabilityManager->reveal());
        $this->assertInstanceOf(RoomAvailabilityUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->delete($uuid)->willThrow(RoomAvailabilityNotFoundException::class);
        $controller = new RoomAvailabilityController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $roomAvailabilityManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->delete($uuid)->shouldBeCalled();
        $controller = new RoomAvailabilityController();
        $response = $controller->delete(Uuid::fromString($uuid), $roomAvailabilityManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\RoomAvailability\RoomAvailabilityCreateResponse::__construct
     * @dataProvider sampleRoomAvailability
     */
    public function testCreate(string $uuid, RoomAvailability $roomAvailability)
    {
        $roomAvailabilityCreateRequest = new RoomAvailabilityCreateRequest();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->create($roomAvailabilityCreateRequest)->willReturn($roomAvailability);
        $controller = new RoomAvailabilityController();
        $roomAvailabilityCreateResponse = $controller->create($roomAvailabilityCreateRequest, $roomAvailabilityManager->reveal());

        $this->assertEquals($uuid, $roomAvailabilityCreateResponse->uuid);
    }

    public function sampleRoomAvailability(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $rateBand = new RoomAvailability();
        $rateBand->uuid = $uuidInterface->reveal();
        $rateBand->roomGoldenId = '1234';
        $rateBand->rateBandGoldenId = 'rb1234';
        $rateBand->stock = 2;
        $rateBand->date = new \DateTime();
        $rateBand->type = 'instant';
        $rateBand->createdAt = new \DateTime();
        $rateBand->updatedAt = new \DateTime();

        yield [$uuid, $rateBand];
    }
}
