<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Controller\BookingController;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\BookingNotFoundException;
use App\Manager\BookingManager;
use App\Tests\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \App\Controller\BookingController
 */
class BookingControllerTest extends ProphecyTestCase
{
    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $controller = new BookingController();
        $bookingCreateRequest = new BookingCreateRequest();
        $manager = $this->prophesize(BookingManager::class);
        $manager->create($bookingCreateRequest)->shouldBeCalled();
        $response = $controller->create($bookingCreateRequest, $manager->reveal());

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @covers ::update
     */
    public function testUpdate()
    {
        $controller = new BookingController();
        $bookingUpdateRequest = new BookingUpdateRequest();
        $manager = $this->prophesize(BookingManager::class);
        $manager->update($bookingUpdateRequest)->shouldBeCalled();
        $response = $controller->update($bookingUpdateRequest, $manager->reveal());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @covers ::update
     */
    public function testUpdateCatchesException()
    {
        $controller = new BookingController();
        $bookingUpdateRequest = new BookingUpdateRequest();
        $manager = $this->prophesize(BookingManager::class);
        $manager->update($bookingUpdateRequest)->shouldBeCalled()->willThrow(BookingNotFoundException::class);
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Booking not found');
        $response = $controller->update($bookingUpdateRequest, $manager->reveal());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
