<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Contract\Response\Booking\BookingGetResponse;
use App\Controller\Api\BookingController;
use App\Entity\Booking;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BookingManager;
use PHPUnit\Framework\TestCase;
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
        $bookingManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $bookingManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Booking\BookingGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new BookingController();
        $bookingManager = $this->prophesize(BookingManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $bookingManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Booking\BookingGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $currentDate = new \DateTime();
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
        $booking->createdAt = $currentDate;
        $booking->updatedAt = $currentDate;

        $controller = new BookingController();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->get($uuid)->willReturn($booking);
        $return = $controller->get($uuid, $bookingManager->reveal());
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
        $this->assertEquals($booking->customerExternalId, $return->customerExternalId);
        $this->assertEquals($booking->customerFirstName, $return->customerFirstName);
        $this->assertEquals($booking->customerLastName, $return->customerLastName);
        $this->assertEquals($booking->customerEmail, $return->customerEmail);
        $this->assertEquals($booking->customerPhone, $return->customerPhone);
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
        $bookingManager->update($uuid, $bookingUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new BookingController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $bookingUpdateRequest, $bookingManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingUpdateRequest = new BookingUpdateRequest();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->update($uuid, $bookingUpdateRequest)->shouldBeCalled();
        $controller = new BookingController();
        $response = $controller->put($uuid, $bookingUpdateRequest, $bookingManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $bookingManager = $this->prophesize(BookingManager::class);
        $controller = new BookingController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $bookingManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new BookingController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $bookingManager->reveal());
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
        $response = $controller->delete($uuid, $bookingManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Booking\BookingCreateResponse::__construct
     */
    public function testCreate()
    {
        $bookingCreateRequest = new BookingCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $booking = new Booking();
        $booking->uuid = $uuidInterface->reveal();
        $bookingManager = $this->prophesize(BookingManager::class);
        $bookingManager->create($bookingCreateRequest)->willReturn($booking);
        $controller = new BookingController();
        $bookingCreateResponse = $controller->create($bookingCreateRequest, $bookingManager->reveal());

        $this->assertEquals($uuid, $bookingCreateResponse->uuid);
    }
}
