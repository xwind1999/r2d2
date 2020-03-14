<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;
use App\Entity\BookingDate;
use App\Entity\RateBand;
use App\Entity\Room;
use App\Manager\BookingDateManager;
use App\Repository\BookingDateRepository;
use App\Repository\RateBandRepository;
use App\Repository\RoomRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\BookingDateManager
 */
class BookingDateManagerTest extends TestCase
{
    /**
     * @var BookingDateRepository|ObjectProphecy
     */
    protected $repository;

    /**
     * @var ObjectProphecy|RoomRepository
     */
    protected $roomRepository;

    /**
     * @var ObjectProphecy|RateBandRepository
     */
    protected $rateBandRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(BookingDateRepository::class);
        $this->roomRepository = $this->prophesize(RoomRepository::class);
        $this->rateBandRepository = $this->prophesize(RateBandRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new BookingDateManager($this->repository->reveal(), $this->roomRepository->reveal(), $this->rateBandRepository->reveal());
        $room = new Room();
        $room->goldenId = '1234';
        $rateBand = new RateBand();
        $rateBand->goldenId = '7895';
        $this->roomRepository->findOneByGoldenId('1234')->willReturn($room);
        $this->rateBandRepository->findOneByGoldenId('7895')->willReturn($rateBand);
        $bookingDateUpdateRequest = new BookingDateUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $currentDate = new \DateTime();
        $bookingDateUpdateRequest->bookingGoldenId = '5566';
        $bookingDateUpdateRequest->roomGoldenId = '1234';
        $bookingDateUpdateRequest->rateBandGoldenId = '7895';
        $bookingDateUpdateRequest->date = $currentDate;
        $bookingDateUpdateRequest->price = 10;
        $bookingDateUpdateRequest->isUpsell = false;
        $bookingDateUpdateRequest->guestsCount = 1;

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $bookingDate = new BookingDate();
        $bookingDate->uuid = $uuidInterface->reveal();
        $bookingDate->bookingGoldenId = '1234';
        $bookingDate->date = $currentDate;
        $bookingDate->price = 10;
        $bookingDate->isUpsell = true;
        $bookingDate->guestsCount = 1;
        $this->repository->findOne($uuid)->willReturn($bookingDate);

        $this->repository->save(Argument::type(BookingDate::class))->shouldBeCalled();

        $updatedBookingDate = $manager->update($uuid, $bookingDateUpdateRequest);

        $this->assertSame($bookingDate, $updatedBookingDate);
        $this->assertEquals('1234', $bookingDate->roomGoldenId);
        $this->assertEquals('7895', $bookingDate->rateBandGoldenId);
        $this->assertEquals(10, $bookingDate->price);
        $this->assertEquals($currentDate, $bookingDate->date);
        $this->assertEquals(false, $bookingDate->isUpsell);
        $this->assertEquals(1, $bookingDate->guestsCount);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new BookingDateManager($this->repository->reveal(), $this->roomRepository->reveal(), $this->rateBandRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $bookingDate = new BookingDate();
        $bookingDate->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($bookingDate);

        $this->repository->delete(Argument::type(BookingDate::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new BookingDateManager($this->repository->reveal(), $this->roomRepository->reveal(), $this->rateBandRepository->reveal());
        $room = new Room();
        $room->goldenId = '1234';
        $rateBand = new RateBand();
        $rateBand->goldenId = '7895';
        $this->roomRepository->findOneByGoldenId('5678')->willReturn($room);
        $this->rateBandRepository->findOneByGoldenId('7895')->willReturn($rateBand);
        $currentDate = new \DateTime();

        $bookingDateCreateRequest = new BookingDateCreateRequest();
        $bookingDateCreateRequest->bookingGoldenId = '1234';
        $bookingDateCreateRequest->roomGoldenId = '5678';
        $bookingDateCreateRequest->rateBandGoldenId = '7895';
        $bookingDateCreateRequest->date = $currentDate;
        $bookingDateCreateRequest->price = 20;
        $bookingDateCreateRequest->isUpsell = false;
        $bookingDateCreateRequest->guestsCount = 1;

        $this->repository->save(Argument::type(BookingDate::class))->shouldBeCalled();

        $bookingDate = $manager->create($bookingDateCreateRequest);

        $this->assertEquals($bookingDateCreateRequest->roomGoldenId, $bookingDate->roomGoldenId);
        $this->assertEquals($bookingDateCreateRequest->rateBandGoldenId, $bookingDate->rateBandGoldenId);
        $this->assertEquals($bookingDateCreateRequest->date, $bookingDate->date);
        $this->assertEquals($bookingDateCreateRequest->isUpsell, $bookingDate->isUpsell);
        $this->assertEquals($bookingDateCreateRequest->guestsCount, $bookingDate->guestsCount);
    }
}
