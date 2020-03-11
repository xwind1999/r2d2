<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\RoomPrice\RoomPriceUpdateRequest;
use App\Entity\RoomPrice;
use App\Manager\RoomPriceManager;
use App\Repository\RoomPriceRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var ObjectProphecy|RoomPriceRepository
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(RoomPriceRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new RoomPriceManager($this->em->reveal(), $this->repository->reveal());
        $roomPriceUpdateRequest = new RoomPriceUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $currentDate = new \DateTime();
        $roomPriceUpdateRequest->roomGoldenId = '1234';
        $roomPriceUpdateRequest->rateBandGoldenId = '7895';
        $roomPriceUpdateRequest->date = $currentDate;
        $roomPriceUpdateRequest->price = 10;

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $roomPrice->roomGoldenId = '5678';
        $roomPrice->rateBandGoldenId = '7895';
        $roomPrice->date = $currentDate;
        $roomPrice->price = 10;
        $this->repository->findOne($uuid)->willReturn($roomPrice);

        $this->em->persist(Argument::type(RoomPrice::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($uuid, $roomPriceUpdateRequest);

        $this->assertEquals('1234', $roomPrice->roomGoldenId);
        $this->assertEquals('7895', $roomPrice->rateBandGoldenId);
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
        $manager = new RoomPriceManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $roomPrice = new RoomPrice();
        $roomPrice->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($roomPrice);

        $this->em->remove(Argument::type(RoomPrice::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new RoomPriceManager($this->em->reveal(), $this->repository->reveal());
        $currentDate = new \DateTime();

        $roomPriceCreateRequest = new RoomPriceCreateRequest();
        $roomPriceCreateRequest->roomGoldenId = '1234';
        $roomPriceCreateRequest->rateBandGoldenId = '5678';
        $roomPriceCreateRequest->date = $currentDate;
        $roomPriceCreateRequest->price = 20;

        $this->em->persist(Argument::type(RoomPrice::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $roomPrice = $manager->create($roomPriceCreateRequest);

        $this->assertEquals($roomPriceCreateRequest->roomGoldenId, $roomPrice->roomGoldenId);
        $this->assertEquals($roomPriceCreateRequest->rateBandGoldenId, $roomPrice->rateBandGoldenId);
        $this->assertEquals($roomPriceCreateRequest->date, $roomPrice->date);
        $this->assertEquals($roomPriceCreateRequest->price, $roomPrice->price);
    }
}
