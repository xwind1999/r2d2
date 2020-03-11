<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;
use App\Contract\Response\BookingDate\BookingDateGetResponse;
use App\Controller\Api\BookingDateController;
use App\Entity\BookingDate;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BookingDateManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @coversDefaultClass \App\Controller\Api\BookingDateController
 */
class BookingDateControllerTest extends TestCase
{
    /**
     * @covers ::get
     * @covers \App\Contract\Response\BookingDate\BookingDateGetResponse::__construct
     */
    public function testIfGetWillThrowResourceNotFoundException(): void
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $controller = new BookingDateController();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->get($uuid)->willThrow(EntityNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $controller->get($uuid, $bookingDateManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\BookingDate\BookingDateGetResponse::__construct
     */
    public function testIfGetWillThrowUnprocessableEntityException(): void
    {
        $controller = new BookingDateController();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $this->expectException(UnprocessableEntityException::class);
        $controller->get('12345', $bookingDateManager->reveal());
    }

    /**
     * @covers ::get
     * @covers \App\Contract\Response\BookingDate\BookingDateGetResponse::__construct
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
        $bookingDate->roomGoldenId = '5678';
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
        $return = $controller->get($uuid, $bookingDateManager->reveal());
        $this->assertEquals(BookingDateGetResponse::class, get_class($return));
        $this->assertEquals($uuid, $return->uuid);
        $this->assertEquals($bookingDate->bookingGoldenId, $return->bookingGoldenId);
        $this->assertEquals($bookingDate->roomGoldenId, $return->roomGoldenId);
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
        $bookingDateManager->update($uuid, $bookingDateUpdateRequest)->willThrow(EntityNotFoundException::class);
        $controller = new BookingDateController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->put($uuid, $bookingDateUpdateRequest, $bookingDateManager->reveal());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingDateUpdateRequest = new BookingDateUpdateRequest();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->update($uuid, $bookingDateUpdateRequest)->shouldBeCalled();
        $controller = new BookingDateController();
        $response = $controller->put($uuid, $bookingDateUpdateRequest, $bookingDateManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowUnprocessableEntityException()
    {
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $controller = new BookingDateController();
        $this->expectException(UnprocessableEntityException::class);
        $controller->delete('1234', $bookingDateManager->reveal());
    }

    /**
     * @covers ::delete
     */
    public function testIfDeleteWillThrowResourceNotFoundException()
    {
        $uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->delete($uuid)->willThrow(EntityNotFoundException::class);
        $controller = new BookingDateController();
        $this->expectException(ResourceNotFoundException::class);
        $controller->delete($uuid, $bookingDateManager->reveal());
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
        $response = $controller->delete($uuid, $bookingDateManager->reveal());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * @covers ::create
     * @covers \App\Contract\Response\BookingDate\BookingDateCreateResponse::__construct
     */
    public function testCreate()
    {
        $bookingDateCreateRequest = new BookingDateCreateRequest();
        $uuid = '1234';
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn($uuid);
        $bookingDate = new BookingDate();
        $bookingDate->uuid = $uuidInterface->reveal();
        $bookingDateManager = $this->prophesize(BookingDateManager::class);
        $bookingDateManager->create($bookingDateCreateRequest)->willReturn($bookingDate);
        $controller = new BookingDateController();
        $bookingDateCreateResponse = $controller->create($bookingDateCreateRequest, $bookingDateManager->reveal());

        $this->assertEquals($uuid, $bookingDateCreateResponse->uuid);
    }
}
