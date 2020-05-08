<?php

declare(strict_types=1);

namespace App\Tests\Controller\Internal;

use App\Contract\Request\Internal\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\Internal\BookingDate\BookingDateUpdateRequest;
use App\Contract\Response\Internal\BookingDate\BookingDateGetResponse;
use App\Contract\Response\Internal\BookingDate\BookingDateUpdateResponse;
use App\Controller\Api\BookingDateController;
use App\Entity\BookingDate;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\BookingDateNotFoundException;
use App\Manager\BookingDateManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\BookingDateController
 */
class BookingDateControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\BookingDate\BookingDateGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new BookingDateController();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->get($uuid)->willThrow(BookingDateNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get(Uuid::fromString($uuid), $bookingDateManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\Internal\BookingDate\BookingDateGetResponse::__construct
     */
    public function testGet(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $currentDate = new \DateTime();
        $bookingDate = new BookingDate();
        $bookingDate->uuid = $uuidInterface->reveal();
        $bookingDate->bookingGoldenId = '1234';
        $bookingDate->componentGoldenId = '5678';
        $bookingDate->rateBandGoldenId = '7895';
        $bookingDate->date = $currentDate;
        $bookingDate->price = 10;
        $bookingDate->isUpsell = false;
        $bookingDate->guestsCount = 1;
        $bookingDate->createdAt = new \DateTime();
        $bookingDate->updatedAt = new \DateTime();

        $controller = new BookingDateController();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->get($uuid)->willReturn($bookingDate);
        $return = $controller->get(Uuid::fromString($uuid), $bookingDateManager->reveal());
        $this->assertEquals(BookingDateGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($bookingDate->bookingGoldenId, $return->bookingGoldenId);
        $this->assertEquals($bookingDate->componentGoldenId, $return->componentGoldenId);
        $this->assertEquals($bookingDate->rateBandGoldenId, $return->rateBandGoldenId);
        $this->assertEquals($bookingDate->date, $return->date);
        $this->assertEquals($bookingDate->price, $return->price);
        $this->assertEquals($bookingDate->isUpsell, $return->isUpsell);
        $this->assertEquals($bookingDate->guestsCount, $return->guestsCount);
        $this->assertEquals($bookingDate->createdAt, $return->createdAt);
        $this->assertEquals($bookingDate->updatedAt, $return->updatedAt);
    }

    /**
     * @covers ::put
     */
    public function testIfPutThrowsResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingDateUpdateRequest = new BookingDateUpdateRequest();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->update($uuid, $bookingDateUpdateRequest)->willThrow(BookingDateNotFoundException::class);
        $controller = new BookingDateController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put(Uuid::fromString($uuid), $bookingDateUpdateRequest, $bookingDateManager->reveal());
    }

    /**
     * @covers ::put
     * @dataProvider sampleBookingDate
     */
    public function testPut(string $uuid, BookingDate $bookingDate)
    {
        $bookingDateUpdateRequest = new BookingDateUpdateRequest();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->update($uuid, $bookingDateUpdateRequest)->shouldBeCalled()->willReturn($bookingDate);
        $controller = new BookingDateController();
        $response = $controller->put(Uuid::fromString($uuid), $bookingDateUpdateRequest, $bookingDateManager->reveal());
        $this->assertInstanceOf(BookingDateUpdateResponse::class, $response);
        $this->assertEquals(200, $response->getHttpCode());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->delete($uuid)->willThrow(BookingDateNotFoundException::class);
        $controller = new BookingDateController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete(Uuid::fromString($uuid), $bookingDateManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->delete($uuid)->shouldBeCalled();
        $controller = new BookingDateController();
        $response = $controller->delete(Uuid::fromString($uuid), $bookingDateManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\Internal\BookingDate\BookingDateCreateResponse::__construct
     * @dataProvider sampleBookingDate
     */
    public function testCreate(string $uuid, BookingDate $bookingDate)
    {
        $bookingDateCreateRequest = new BookingDateCreateRequest();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->create($bookingDateCreateRequest)->willReturn($bookingDate);
        $controller = new BookingDateController();
        $bookingDateCreateResponse = $controller->create($bookingDateCreateRequest, $bookingDateManager->reveal());

        $this->assertEquals($uuid, $bookingDateCreateResponse->uuid);
    }

    public function sampleBookingDate(): iterable
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $bookingDate = new BookingDate();
        $bookingDate->uuid = $uuidInterface->reveal();
        $bookingDate->bookingGoldenId = '1234';
        $bookingDate->componentGoldenId = '1234';
        $bookingDate->rateBandGoldenId = '1234';
        $bookingDate->date = new \DateTime('2020-01-01');
        $bookingDate->price = 9990;
        $bookingDate->isUpsell = true;
        $bookingDate->guestsCount = 1;
        $bookingDate->createdAt = new \DateTime();
        $bookingDate->updatedAt = new \DateTime();

        yield [$uuid, $bookingDate];
    }
}
