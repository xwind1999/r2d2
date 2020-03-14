<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Entity\Booking;
use App\Entity\Experience;
use App\Entity\Partner;
use App\Manager\BookingManager;
use App\Repository\BookingRepository;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Manager\BookingManager
 */
class BookingManagerTest extends TestCase
{
    /**
     * @var BookingRepository|ObjectProphecy
     */
    protected $repository;

    /**
     * @var ObjectProphecy|PartnerRepository
     */
    protected $partnerRepository;

    /**
     * @var ExperienceRepository|ObjectProphecy
     */
    protected $experienceRepository;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(BookingRepository::class);
        $this->partnerRepository = $this->prophesize(PartnerRepository::class);
        $this->experienceRepository = $this->prophesize(ExperienceRepository::class);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::update
     */
    public function testUpdate()
    {
        $manager = new BookingManager($this->repository->reveal(), $this->partnerRepository->reveal(), $this->experienceRepository->reveal());

        $partner = new Partner();
        $partner->goldenId = '5432';
        $this->partnerRepository->findOneByGoldenId('5432')->willReturn($partner);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $bookingUpdateRequest = new BookingUpdateRequest();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $currentDate = new \DateTime();

        $bookingUpdateRequest->goldenId = '9876';
        $bookingUpdateRequest->partnerGoldenId = '5432';
        $bookingUpdateRequest->experienceGoldenId = '9012';
        $bookingUpdateRequest->type = 'booking';
        $bookingUpdateRequest->voucher = '123456789';
        $bookingUpdateRequest->brand = 'sbx';
        $bookingUpdateRequest->country = 'fr';
        $bookingUpdateRequest->requestType = 'instant';
        $bookingUpdateRequest->channel = 'web';
        $bookingUpdateRequest->cancellationChannel = null;
        $bookingUpdateRequest->status = 'complete';
        $bookingUpdateRequest->totalPrice = 150;
        $bookingUpdateRequest->startDate = new \DateTime('2020-05-05');
        $bookingUpdateRequest->endDate = new \DateTime('2020-05-06');
        $bookingUpdateRequest->customerExternalId = 'W123123';
        $bookingUpdateRequest->customerFirstName = 'new name';
        $bookingUpdateRequest->customerLastName = 'new last name';
        $bookingUpdateRequest->customerEmail = 'a@a.com';
        $bookingUpdateRequest->customerPhone = '55566666';
        $bookingUpdateRequest->customerComment = 'i want a double bed';
        $bookingUpdateRequest->partnerComment = 'i dont have double bed';
        $bookingUpdateRequest->placedAt = $currentDate;
        $bookingUpdateRequest->cancelledAt = $currentDate;

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);

        $booking = new Booking();
        $booking->uuid = $uuidInterface->reveal();
        $booking->goldenId = '1234';
        $booking->partnerGoldenId = '5678';
        $booking->experienceGoldenId = '9012';
        $booking->type = 'booking';
        $booking->voucher = '123456789';
        $booking->brand = 'sbx';
        $booking->country = 'fr';
        $booking->requestType = 'instant';
        $booking->channel = 'web';
        $booking->cancellationChannel = null;
        $booking->status = 'complete';
        $booking->totalPrice = 150;
        $booking->startDate = new \DateTime('2020-05-05');
        $booking->endDate = new \DateTime('2020-05-06');
        $booking->customerExternalId = 'W123123';
        $booking->customerFirstName = null;
        $booking->customerLastName = null;
        $booking->customerEmail = null;
        $booking->customerPhone = null;
        $booking->customerComment = null;
        $booking->partnerComment = null;
        $booking->placedAt = $currentDate;
        $booking->cancelledAt = null;
        $this->repository->findOne($uuid)->willReturn($booking);

        $this->repository->save(Argument::type(Booking::class))->shouldBeCalled();

        $manager->update($uuid, $bookingUpdateRequest);
        $this->assertEquals('9876', $booking->goldenId);
        $this->assertEquals('5432', $booking->partnerGoldenId);
        $this->assertEquals('9012', $booking->experienceGoldenId);
        $this->assertEquals('booking', $booking->type);
        $this->assertEquals('123456789', $booking->voucher);
        $this->assertEquals('sbx', $booking->brand);
        $this->assertEquals('fr', $booking->country);
        $this->assertEquals('instant', $booking->requestType);
        $this->assertEquals('web', $booking->channel);
        $this->assertEquals(null, $booking->cancellationChannel);
        $this->assertEquals('complete', $booking->status);
        $this->assertEquals(150, $booking->totalPrice);
        $this->assertEquals(new \DateTime('2020-05-05'), $booking->startDate);
        $this->assertEquals(new \DateTime('2020-05-06'), $booking->endDate);
        $this->assertEquals('W123123', $booking->customerExternalId);
        $this->assertEquals('new name', $booking->customerFirstName);
        $this->assertEquals('new last name', $booking->customerLastName);
        $this->assertEquals('a@a.com', $booking->customerEmail);
        $this->assertEquals('55566666', $booking->customerPhone);
        $this->assertEquals('i want a double bed', $booking->customerComment);
        $this->assertEquals('i dont have double bed', $booking->partnerComment);
        $this->assertEquals($currentDate, $booking->placedAt);
        $this->assertEquals($currentDate, $booking->cancelledAt);
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::delete
     */
    public function testDelete()
    {
        $manager = new BookingManager($this->repository->reveal(), $this->partnerRepository->reveal(), $this->experienceRepository->reveal());
        $uuid = '12345678';

        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $booking = new Booking();
        $booking->uuid = $uuidInterface->reveal();
        $this->repository->findOne($uuid)->willReturn($booking);

        $this->repository->delete(Argument::type(Booking::class))->shouldBeCalled();

        $manager->delete($uuid);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreate()
    {
        $manager = new BookingManager($this->repository->reveal(), $this->partnerRepository->reveal(), $this->experienceRepository->reveal());

        $partner = new Partner();
        $partner->goldenId = '5678';
        $this->partnerRepository->findOneByGoldenId('5678')->willReturn($partner);

        $experience = new Experience();
        $experience->goldenId = '9012';
        $this->experienceRepository->findOneByGoldenId('9012')->willReturn($experience);

        $currentDate = new \DateTime();

        $bookingCreateRequest = new BookingCreateRequest();

        $bookingCreateRequest->goldenId = '1234';
        $bookingCreateRequest->partnerGoldenId = '5678';
        $bookingCreateRequest->experienceGoldenId = '9012';
        $bookingCreateRequest->type = 'booking';
        $bookingCreateRequest->voucher = '123456789';
        $bookingCreateRequest->brand = 'sbx';
        $bookingCreateRequest->country = 'fr';
        $bookingCreateRequest->requestType = 'instant';
        $bookingCreateRequest->channel = 'web';
        $bookingCreateRequest->cancellationChannel = null;
        $bookingCreateRequest->status = 'complete';
        $bookingCreateRequest->totalPrice = 150;
        $bookingCreateRequest->startDate = new \DateTime('2020-05-05');
        $bookingCreateRequest->endDate = new \DateTime('2020-05-06');
        $bookingCreateRequest->customerExternalId = 'W123123';
        $bookingCreateRequest->customerFirstName = null;
        $bookingCreateRequest->customerLastName = null;
        $bookingCreateRequest->customerEmail = null;
        $bookingCreateRequest->customerPhone = null;
        $bookingCreateRequest->customerComment = null;
        $bookingCreateRequest->partnerComment = null;
        $bookingCreateRequest->placedAt = $currentDate;
        $bookingCreateRequest->cancelledAt = null;

        $this->repository->save(Argument::type(Booking::class))->shouldBeCalled();

        $booking = $manager->create($bookingCreateRequest);
        $this->assertEquals($bookingCreateRequest->goldenId, $booking->goldenId);
        $this->assertEquals($bookingCreateRequest->partnerGoldenId, $booking->partnerGoldenId);
        $this->assertEquals($bookingCreateRequest->experienceGoldenId, $booking->experienceGoldenId);
        $this->assertEquals($bookingCreateRequest->type, $booking->type);
        $this->assertEquals($bookingCreateRequest->voucher, $booking->voucher);
        $this->assertEquals($bookingCreateRequest->brand, $booking->brand);
        $this->assertEquals($bookingCreateRequest->country, $booking->country);
        $this->assertEquals($bookingCreateRequest->requestType, $booking->requestType);
        $this->assertEquals($bookingCreateRequest->channel, $booking->channel);
        $this->assertEquals($bookingCreateRequest->cancellationChannel, $booking->cancellationChannel);
        $this->assertEquals($bookingCreateRequest->status, $booking->status);
        $this->assertEquals($bookingCreateRequest->totalPrice, $booking->totalPrice);
        $this->assertEquals($bookingCreateRequest->startDate, $booking->startDate);
        $this->assertEquals($bookingCreateRequest->endDate, $booking->endDate);
        $this->assertEquals($bookingCreateRequest->customerExternalId, $booking->customerExternalId);
        $this->assertEquals($bookingCreateRequest->customerFirstName, $booking->customerFirstName);
        $this->assertEquals($bookingCreateRequest->customerLastName, $booking->customerLastName);
        $this->assertEquals($bookingCreateRequest->customerEmail, $booking->customerEmail);
        $this->assertEquals($bookingCreateRequest->customerPhone, $booking->customerPhone);
        $this->assertEquals($bookingCreateRequest->customerComment, $booking->customerComment);
        $this->assertEquals($bookingCreateRequest->partnerComment, $booking->partnerComment);
        $this->assertEquals($bookingCreateRequest->placedAt, $booking->placedAt);
        $this->assertEquals($bookingCreateRequest->cancelledAt, $booking->cancelledAt);
    }
}
