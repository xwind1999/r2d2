<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Internal\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\Internal\BookingDate\BookingDateUpdateRequest;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Component;
use App\Entity\RateBand;
use App\Manager\BookingDateManager;
use App\Repository\BookingDateRepository;
use App\Repository\BookingRepository;
use App\Repository\ComponentRepository;
use App\Repository\RateBandRepository;
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
     * @var ComponentRepository|ObjectProphecy
     */
    protected $componentRepository;

    /**
     * @var ObjectProphecy|RateBandRepository
     */
    protected $rateBandRepository;

    /**
     * @var BookingRepository|ObjectProphecy
     */
    protected $bookingRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(BookingDateRepository::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->rateBandRepository = $this->prophesize(RateBandRepository::class);
        $this->bookingRepository = $this->prophesize(BookingRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new BookingDateManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->rateBandRepository->reveal(), $this->bookingRepository->reveal());

        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('1234')->willReturn($component);

        $rateBand = new RateBand();
        $rateBand->goldenId = '7895';
        $this->rateBandRepository->findOneByGoldenId('7895')->willReturn($rateBand);

        $booking = new Booking();
        $booking->goldenId = '5566';
        $this->bookingRepository->findOneByGoldenId('5566')->willReturn($booking);

        $bookingDateUpdateRequest = new BookingDateUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $currentDate = new \DateTime();
        $bookingDateUpdateRequest->bookingGoldenId = '5566';
        $bookingDateUpdateRequest->componentGoldenId = '1234';
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
        $this->assertEquals('1234', $bookingDate->componentGoldenId);
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
        $manager = new BookingDateManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->rateBandRepository->reveal(), $this->bookingRepository->reveal());
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
        $manager = new BookingDateManager($this->repository->reveal(), $this->componentRepository->reveal(), $this->rateBandRepository->reveal(), $this->bookingRepository->reveal());

        $component = new Component();
        $component->goldenId = '1234';
        $this->componentRepository->findOneByGoldenId('5678')->willReturn($component);

        $rateBand = new RateBand();
        $rateBand->goldenId = '7895';
        $this->rateBandRepository->findOneByGoldenId('7895')->willReturn($rateBand);

        $booking = new Booking();
        $booking->goldenId = '1234';
        $this->bookingRepository->findOneByGoldenId('1234')->willReturn($booking);

        $currentDate = new \DateTime();

        $bookingDateCreateRequest = new BookingDateCreateRequest();
        $bookingDateCreateRequest->bookingGoldenId = '1234';
        $bookingDateCreateRequest->componentGoldenId = '5678';
        $bookingDateCreateRequest->rateBandGoldenId = '7895';
        $bookingDateCreateRequest->date = $currentDate;
        $bookingDateCreateRequest->price = 20;
        $bookingDateCreateRequest->isUpsell = false;
        $bookingDateCreateRequest->guestsCount = 1;

        $this->repository->save(Argument::type(BookingDate::class))->shouldBeCalled();

        $bookingDate = $manager->create($bookingDateCreateRequest);

        $this->assertEquals($bookingDateCreateRequest->componentGoldenId, $bookingDate->componentGoldenId);
        $this->assertEquals($bookingDateCreateRequest->rateBandGoldenId, $bookingDate->rateBandGoldenId);
        $this->assertEquals($bookingDateCreateRequest->date, $bookingDate->date);
        $this->assertEquals($bookingDateCreateRequest->isUpsell, $bookingDate->isUpsell);
        $this->assertEquals($bookingDateCreateRequest->guestsCount, $bookingDate->guestsCount);
    }
}
