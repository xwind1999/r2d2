<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\RoomAvailability;
use App\Manager\RoomAvailabilityManager;
use App\Repository\RoomAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\RoomAvailabilityManager
 */
class RoomAvailabilityManagerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var ObjectProphecy|RoomAvailabilityRepository
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(RoomAvailabilityRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomAvailabilityManager($this->em->reveal(), $this->repository->reveal());
        $roomAvailabilityUpdateRequest = new RoomAvailabilityUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityUpdateRequest->roomGoldenId = '1234';
        $roomAvailabilityUpdateRequest->rateBandGoldenId = '5678';
        $roomAvailabilityUpdateRequest->stock = 2;
        $roomAvailabilityUpdateRequest->date = new \DateTime('2020-01-01');
        $roomAvailabilityUpdateRequest->type = 'instant';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $roomAvailability->roomGoldenId = '4321';
        $roomAvailability->rateBandGoldenId = '8765';
        $roomAvailability->stock = 3;
        $roomAvailability->date = new \DateTime('2020-01-02');
        $roomAvailability->type = 'instant';
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $this->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($uuid, $roomAvailabilityUpdateRequest);

        $this->assertEquals('1234', $roomAvailability->roomGoldenId);
        $this->assertEquals('5678', $roomAvailability->rateBandGoldenId);
        $this->assertEquals('2', $roomAvailability->stock);
        $this->assertEquals('2020-01-01', $roomAvailability->date->format('Y-m-d'));
        $this->assertEquals('instant', $roomAvailability->type);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new RoomAvailabilityManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $this->em->remove(Argument::type(RoomAvailability::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomAvailabilityManager($this->em->reveal(), $this->repository->reveal());
        $roomAvailabilityCreateRequest = new RoomAvailabilityCreateRequest();
        $roomAvailabilityCreateRequest->roomGoldenId = '1234';
        $roomAvailabilityCreateRequest->rateBandGoldenId = '5678';
        $roomAvailabilityCreateRequest->stock = 2;
        $roomAvailabilityCreateRequest->date = new \DateTime('2020-01-01');
        $roomAvailabilityCreateRequest->type = 'instant';

        $this->em->persist(Argument::type(RoomAvailability::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $roomAvailability = $manager->create($roomAvailabilityCreateRequest);
        $this->assertEquals($roomAvailabilityCreateRequest->roomGoldenId, $roomAvailability->roomGoldenId);
        $this->assertEquals($roomAvailabilityCreateRequest->rateBandGoldenId, $roomAvailability->rateBandGoldenId);
        $this->assertEquals($roomAvailabilityCreateRequest->stock, $roomAvailability->stock);
        $this->assertEquals($roomAvailabilityCreateRequest->date, $roomAvailability->date);
        $this->assertEquals($roomAvailabilityCreateRequest->type, $roomAvailability->type);
    }
}
