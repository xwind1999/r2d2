<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;
use App\Entity\Room;
use App\Manager\RoomManager;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var ObjectProphecy|RoomRepository
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(RoomRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomManager($this->em->reveal(), $this->repository->reveal());
        $roomUpdateRequest = new RoomUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomUpdateRequest->uuid = $uuid;
        $roomUpdateRequest->goldenId = '1234';
        $roomUpdateRequest->partnerGoldenId = '4321';
        $roomUpdateRequest->name = 'room with a big big bed';
        $roomUpdateRequest->description = 'the bed is so big it could fit two families';
        $roomUpdateRequest->inventory = 2;
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
        $room->isSellable = false;
        $room->status = 'ok';
        $this->repository->findOne($uuid)->willReturn($room);

        $this->em->persist(Argument::type(Room::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($uuid, $roomUpdateRequest);

        $this->assertEquals(2, $room->inventory);
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
        $manager = new RoomManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $room = new Room();
        $room->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($room);

        $this->em->remove(Argument::type(Room::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomManager($this->em->reveal(), $this->repository->reveal());
        $roomCreateRequest = new RoomCreateRequest();
        $roomCreateRequest->goldenId = '5678';
        $roomCreateRequest->partnerGoldenId = '5678';
        $roomCreateRequest->name = 'room with small bed';
        $roomCreateRequest->description = 'the bed is very small';
        $roomCreateRequest->inventory = 1;
        $roomCreateRequest->isSellable = false;
        $roomCreateRequest->status = 'ok';

        $this->em->persist(Argument::type(Room::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $room = $manager->create($roomCreateRequest);
        $this->assertEquals($roomCreateRequest->goldenId, $room->goldenId);
        $this->assertEquals($roomCreateRequest->partnerGoldenId, $room->partnerGoldenId);
        $this->assertEquals($roomCreateRequest->name, $room->name);
        $this->assertEquals($roomCreateRequest->description, $room->description);
        $this->assertEquals($roomCreateRequest->inventory, $room->inventory);
        $this->assertEquals($roomCreateRequest->isSellable, $room->isSellable);
        $this->assertEquals($roomCreateRequest->status, $room->status);
    }
}
