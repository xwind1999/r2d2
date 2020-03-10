<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Contract\Response\RoomAvailability\RoomAvailabilityGetResponse;
use App\Controller\Api\RoomAvailabilityController;
use App\Entity\RoomAvailability;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomAvailabilityManager;
use PHPUnit\Framework\TestCase;
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
        $roomAvailabilityManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $roomAvailabilityManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\RoomAvailability\RoomAvailabilityGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new RoomAvailabilityController();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $roomAvailabilityManager->reveal());
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
        $return = $controller->get($uuid, $roomAvailabilityManager->reveal());
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
        $roomAvailabilityManager->update($uuid, $roomAvailabilityUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new RoomAvailabilityController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $roomAvailabilityUpdateRequest, $roomAvailabilityManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityUpdateRequest = new RoomAvailabilityUpdateRequest();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->update($uuid, $roomAvailabilityUpdateRequest)->shouldBeCalled();
        $controller = new RoomAvailabilityController();
        $response = $controller->put($uuid, $roomAvailabilityUpdateRequest, $roomAvailabilityManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $controller = new RoomAvailabilityController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $roomAvailabilityManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new RoomAvailabilityController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $roomAvailabilityManager->reveal());
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
        $response = $controller->delete($uuid, $roomAvailabilityManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\RoomAvailability\RoomAvailabilityCreateResponse::__construct
     */
    public function testCreate()
    {
        $roomAvailabilityCreateRequest = new RoomAvailabilityCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $roomAvailabilityManager->create($roomAvailabilityCreateRequest)->willReturn($roomAvailability);
        $controller = new RoomAvailabilityController();
        $roomAvailabilityCreateResponse = $controller->create($roomAvailabilityCreateRequest, $roomAvailabilityManager->reveal());

        $this->assertEquals($uuid, $roomAvailabilityCreateResponse->uuid);
    }
}
