<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Contract\Response\Booking\BookingGetResponse;
use App\Contract\Response\Booking\BookingUpdateResponse;
use App\Controller\Api\BookingController;
use App\Entity\Booking;
use App\Entity\Guest;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\BookingNotFoundException;
use App\Manager\BookingManager;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\BookingController
 */
class BookingControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Booking\BookingGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new BookingController();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->get($uuid)->willThrow(BookingNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $bookingManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Booking\BookingGetResponse::__construct
     * @dataProvider sampleBooking
     */
    public function testGet(string $uuid, Booking $booking): void
    {
        $controller = new BookingController();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->get($uuid)->willReturn($booking);
        $return = $controller->get(Uuid::fromString($uuid), $bookingManager->reveal());
        $this->assertEquals(BookingGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($booking->goldenId, $return->goldenId);
        $this->assertEquals($booking->partnerGoldenId, $return->partnerGoldenId);
        $this->assertEquals($booking->experienceGoldenId, $return->experienceGoldenId);
        $this->assertEquals($booking->type, $return->type);
        $this->assertEquals($booking->voucher, $return->voucher);
        $this->assertEquals($booking->brand, $return->brand);
        $this->assertEquals($booking->country, $return->country);
        $this->assertEquals($booking->requestType, $return->requestType);
        $this->assertEquals($booking->channel, $return->channel);
        $this->assertEquals($booking->cancellationChannel, $return->cancellationChannel);
        $this->assertEquals($booking->status, $return->status);
        $this->assertEquals($booking->totalPrice, $return->totalPrice);
        $this->assertEquals($booking->startDate, $return->startDate);
        $this->assertEquals($booking->endDate, $return->endDate);
        $this->assertEquals($booking->guest->first()->externalId, $return->guest[0]->externalId);
        $this->assertEquals($booking->customerComment, $return->customerComment);
        $this->assertEquals($booking->partnerComment, $return->partnerComment);
        $this->assertEquals($booking->placedAt, $return->placedAt);
        $this->assertEquals($booking->cancelledAt, $return->cancelledAt);
        $this->assertEquals($booking->createdAt, $return->createdAt);
        $this->assertEquals($booking->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingUpdateRequest = new BookingUpdateRequest();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->update($uuid, $bookingUpdateRequest)->willThrow(BookingNotFoundException::class);
        $controller = new BookingController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $bookingUpdateRequest, $bookingManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleBooking
     */
    public function testPut(string $uuid, Booking $booking)
    {
        $bookingUpdateRequest = new BookingUpdateRequest();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->update($uuid, $bookingUpdateRequest)->shouldBeCalled()->willReturn($booking);
        $controller = new BookingController();
        $response = $controller->put(Uuid::fromString($uuid), $bookingUpdateRequest, $bookingManager->reveal());
        $this->assertInstanceOf(BookingUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->delete($uuid)->willThrow(BookingNotFoundException::class);
        $controller = new BookingController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $bookingManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->delete($uuid)->shouldBeCalled();
        $controller = new BookingController();
        $response = $controller->delete(Uuid::fromString($uuid), $bookingManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Booking\BookingCreateResponse::__construct
     * @dataProvider sampleBooking
     */
    public function testCreate(string $uuid, Booking $booking)
    {
        $bookingCreateRequest = new BookingCreateRequest();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->create($bookingCreateRequest)->willReturn($booking);
        $controller = new BookingController();
        $bookingCreateResponse = $controller->create($bookingCreateRequest, $bookingManager->reveal());

        $this->assertEquals($uuid, $bookingCreateResponse->uuid);
    }

    public function sampleBooking(): iterable
    {
        $currentDate = new \DateTime();
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $guest = new Guest();
        $guest->externalId = '1234567';
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
        $booking->guest = new ArrayCollection([$guest]);
        $booking->customerComment = null;
        $booking->partnerComment = null;
        $booking->placedAt = $currentDate;
        $booking->cancelledAt = null;
        $booking->createdAt = $currentDate;
        $booking->updatedAt = $currentDate;

        yield [$uuid, $booking];
    }
}
