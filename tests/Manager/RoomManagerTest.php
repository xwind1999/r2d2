<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Entity\Partner;
use App\Entity\Room;
use App\Manager\RoomManager;
use App\Repository\PartnerRepository;
use App\Repository\RoomRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\RoomManager
 */
class RoomManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy|RoomRepository
     */
    protected $repository;

    /**
     * @var ObjectProphecy|PartnerRepository
     */
    protected $partnerRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomRepository::class);
        $this->partnerRepository = $this->prophesize(PartnerRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '4321';
        $this->partnerRepository->findOneByGoldenId('4321')->willReturn($partner);
        $roomUpdateRequest = new RoomUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomUpdateRequest->uuid = $uuid;
        $roomUpdateRequest->goldenId = '1234';
        $roomUpdateRequest->partnerGoldenId = '4321';
        $roomUpdateRequest->name = 'room with a big big bed';
        $roomUpdateRequest->description = 'the bed is so big it could fit two families';
        $roomUpdateRequest->inventory = 2;
        $roomUpdateRequest->duration = 1;
        $roomUpdateRequest->isSellable = true;
        $roomUpdateRequest->status = 'not_ok';

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
        $room->status = 'ok';
        $this->repository->findOne($uuid)->willReturn($room);

        $this->repository->save(Argument::type(Room::class))->shouldBeCalled();

        $updatedRoom = $manager->update($uuid, $roomUpdateRequest);

        $this->assertSame($room, $updatedRoom);
        $this->assertEquals(2, $room->inventory);
        $this->assertEquals(1, $room->duration);
        $this->assertEquals('4321', $room->partnerGoldenId);
        $this->assertEquals('room with a big big bed', $room->name);
        $this->assertEquals('the bed is so big it could fit two families', $room->description);
        $this->assertEquals('1234', $room->goldenId);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new RoomManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $room = new Room();
        $room->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($room);

        $this->repository->delete(Argument::type(Room::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomManager($this->repository->reveal(), $this->partnerRepository->reveal());
        $partner = new Partner();
        $partner->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId('5678')->willReturn($partner);
        $roomCreateRequest = new RoomCreateRequest();
        $roomCreateRequest->goldenId = '5678';
        $roomCreateRequest->partnerGoldenId = '5678';
        $roomCreateRequest->name = 'room with small bed';
        $roomCreateRequest->description = 'the bed is very small';
        $roomCreateRequest->inventory = 1;
        $roomCreateRequest->duration = 0;
        $roomCreateRequest->isSellable = false;
        $roomCreateRequest->status = 'ok';

        $this->repository->save(Argument::type(Room::class))->shouldBeCalled();

        $room = $manager->create($roomCreateRequest);
        $this->assertEquals($roomCreateRequest->goldenId, $room->goldenId);
        $this->assertEquals($roomCreateRequest->partnerGoldenId, $room->partnerGoldenId);
        $this->assertEquals($roomCreateRequest->name, $room->name);
        $this->assertEquals($roomCreateRequest->description, $room->description);
        $this->assertEquals($roomCreateRequest->inventory, $room->inventory);
        $this->assertEquals($roomCreateRequest->duration, $room->duration);
        $this->assertEquals($roomCreateRequest->isSellable, $room->isSellable);
        $this->assertEquals($roomCreateRequest->status, $room->status);
    }
}
