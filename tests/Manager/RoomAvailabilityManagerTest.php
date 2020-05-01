<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Entity\Component;
use App\Entity\RateBand;
use App\Entity\RoomAvailability;
use App\Manager\RoomAvailabilityManager;
use App\Repository\ComponentRepository;
use App\Repository\RateBandRepository;
use App\Repository\RoomAvailabilityRepository;
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
     * @var ObjectProphecy|RoomAvailabilityRepository
     */
    protected $repository;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    protected $componentRepository;

    /**
     * @var ObjectProphecy|RateBandRepository
     */
    protected $rateBandRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomAvailabilityRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->rateBandRepository = $this->prophesize(RateBandRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->rateBandRepository->reveal());
        $component = new Component();
        $component->goldenId = '1234';
        $rateBand = new RateBand();
        $rateBand->goldenId = '5678';
        $this->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
        $this->rateBandRepository->findOneByGoldenId(Argument::any())->willReturn($rateBand);
        $roomAvailabilityUpdateRequest = new RoomAvailabilityUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $roomAvailabilityUpdateRequest->componentGoldenId = '1234';
        $roomAvailabilityUpdateRequest->rateBandGoldenId = '5678';
        $roomAvailabilityUpdateRequest->stock = 2;
        $roomAvailabilityUpdateRequest->date = new \DateTime('2020-01-01');
        $roomAvailabilityUpdateRequest->type = 'instant';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $roomAvailability->componentGoldenId = '4321';
        $roomAvailability->rateBandGoldenId = '8765';
        $roomAvailability->stock = 3;
        $roomAvailability->date = new \DateTime('2020-01-02');
        $roomAvailability->type = 'instant';
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $this->repository->save(Argument::type(RoomAvailability::class))->shouldBeCalled();

        $updatedRoomAvailability = $manager->update($uuid, $roomAvailabilityUpdateRequest);

        $this->assertSame($roomAvailability, $updatedRoomAvailability);
        $this->assertEquals('1234', $roomAvailability->componentGoldenId);
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
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->rateBandRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomAvailability = new RoomAvailability();
        $roomAvailability->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($roomAvailability);

        $this->repository->delete(Argument::type(RoomAvailability::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomAvailabilityManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->rateBandRepository->reveal());
        $component = new Component();
        $component->goldenId = '1234';
        $rateBand = new RateBand();
        $rateBand->goldenId = '7895';
        $this->componentRepository->findOneByGoldenId(Argument::any())->willReturn($component);
        $this->rateBandRepository->findOneByGoldenId(Argument::any())->willReturn($rateBand);
        $roomAvailabilityCreateRequest = new RoomAvailabilityCreateRequest();
        $roomAvailabilityCreateRequest->componentGoldenId = '1234';
        $roomAvailabilityCreateRequest->rateBandGoldenId = '5678';
        $roomAvailabilityCreateRequest->stock = 2;
        $roomAvailabilityCreateRequest->date = new \DateTime('2020-01-01');
        $roomAvailabilityCreateRequest->type = 'instant';

        $this->repository->save(Argument::type(RoomAvailability::class))->shouldBeCalled();

        $roomAvailability = $manager->create($roomAvailabilityCreateRequest);
        $this->assertEquals($roomAvailabilityCreateRequest->componentGoldenId, $roomAvailability->componentGoldenId);
        $this->assertEquals($roomAvailabilityCreateRequest->rateBandGoldenId, $roomAvailability->rateBandGoldenId);
        $this->assertEquals($roomAvailabilityCreateRequest->stock, $roomAvailability->stock);
        $this->assertEquals($roomAvailabilityCreateRequest->date, $roomAvailability->date);
        $this->assertEquals($roomAvailabilityCreateRequest->type, $roomAvailability->type);
    }
}
