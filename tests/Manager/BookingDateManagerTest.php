<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;
use App\Entity\BookingDate;
use App\Manager\BookingDateManager;
use App\Repository\BookingDateRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface|ObjectProphecy
     */
    protected $em;

    /**
     * @var BookingDateRepository|ObjectProphecy
     */
    protected $repository;

    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->repository = $this->prophesize(BookingDateRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new BookingDateManager($this->em->reveal(), $this->repository->reveal());
        $bookingDateUpdateRequest = new BookingDateUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $currentDate = new \DateTime();
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
        $bookingDate->roomGoldenId = '5678';
        $bookingDate->rateBandGoldenId = '7895';
        $bookingDate->date = $currentDate;
        $bookingDate->price = 10;
        $bookingDate->isUpsell = true;
        $bookingDate->guestsCount = 1;
        $this->repository->findOne($uuid)->willReturn($bookingDate);

        $this->em->persist(Argument::type(BookingDate::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->update($uuid, $bookingDateUpdateRequest);

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
        $manager = new BookingDateManager($this->em->reveal(), $this->repository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $bookingDate = new BookingDate();
        $bookingDate->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($bookingDate);

        $this->em->remove(Argument::type(BookingDate::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new BookingDateManager($this->em->reveal(), $this->repository->reveal());
        $currentDate = new \DateTime();

        $bookingDateCreateRequest = new BookingDateCreateRequest();
        $bookingDateCreateRequest->bookingGoldenId = '1234';
        $bookingDateCreateRequest->roomGoldenId = '5678';
        $bookingDateCreateRequest->rateBandGoldenId = '7895';
        $bookingDateCreateRequest->date = $currentDate;
        $bookingDateCreateRequest->price = 20;
        $bookingDateCreateRequest->isUpsell = false;
        $bookingDateCreateRequest->guestsCount = 1;

        $this->em->persist(Argument::type(BookingDate::class))->shouldBeCalled();
        $this->em->flush()->shouldBeCalled();

        $bookingDate = $manager->create($bookingDateCreateRequest);

        $this->assertEquals($bookingDateCreateRequest->roomGoldenId, $bookingDate->roomGoldenId);
        $this->assertEquals($bookingDateCreateRequest->rateBandGoldenId, $bookingDate->rateBandGoldenId);
        $this->assertEquals($bookingDateCreateRequest->date, $bookingDate->date);
        $this->assertEquals($bookingDateCreateRequest->isUpsell, $bookingDate->isUpsell);
        $this->assertEquals($bookingDateCreateRequest->guestsCount, $bookingDate->guestsCount);
    }
}
