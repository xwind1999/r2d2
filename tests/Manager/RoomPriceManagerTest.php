<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Internal\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\Internal\RoomPrice\RoomPriceUpdateRequest;
use App\Entity\Component;
use App\Entity\RoomPrice;
use App\Manager\RoomPriceManager;
use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\RoomPriceManager
 */
class RoomPriceManagerTest extends TestCase
{
    /**
     * @var ObjectProphecy|RoomPriceRepository
     */
    protected $repository;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    protected $componentRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(RoomPriceRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomPriceManager($this->repository->reveal(), $this->componentRepository->reveal());
        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);
        $roomPriceUpdateRequest = new RoomPriceUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $currentDate = new \DateTime();
        $roomPriceUpdateRequest->componentGoldenId = '1234';
        $roomPriceUpdateRequest->date = $currentDate;
        $roomPriceUpdateRequest->price = 10;

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $roomPrice->componentGoldenId = '5678';
        $roomPrice->date = $currentDate;
        $roomPrice->price = 10;
        $this->repository->findOne($uuid)->willReturn($roomPrice);

        $this->repository->save(Argument::type(RoomPrice::class))->shouldBeCalled();

        $updatedRoomPrice = $manager->update($uuid, $roomPriceUpdateRequest);

        $this->assertSame($roomPrice, $updatedRoomPrice);
        $this->assertEquals('1234', $roomPrice->componentGoldenId);
        $this->assertEquals($currentDate, $roomPrice->date);
        $this->assertEquals(10, $roomPrice->price);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new RoomPriceManager($this->repository->reveal(), $this->componentRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($roomPrice);

        $this->repository->delete(Argument::type(RoomPrice::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomPriceManager($this->repository->reveal(), $this->componentRepository->reveal());
        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $currentDate = new \DateTime();

        $roomPriceCreateRequest = new RoomPriceCreateRequest();
        $roomPriceCreateRequest->componentGoldenId = '1234';
        $roomPriceCreateRequest->date = $currentDate;
        $roomPriceCreateRequest->price = 20;

        $this->repository->save(Argument::type(RoomPrice::class))->shouldBeCalled();

        $roomPrice = $manager->create($roomPriceCreateRequest);

        $this->assertEquals($roomPriceCreateRequest->componentGoldenId, $roomPrice->componentGoldenId);
        $this->assertEquals($roomPriceCreateRequest->date, $roomPrice->date);
        $this->assertEquals($roomPriceCreateRequest->price, $roomPrice->price);
    }
}
