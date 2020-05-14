<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Controller\BookingController;
use App\Manager\BookingManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class BookingControllerTest extends TestCase
{
    public function testCreate()
    {
        $controller = new BookingController();
        $bookingCreateRequest = new BookingCreateRequest();
        $manager = $this->prophesize(BookingManager::class);
        $manager->create($bookingCreateRequest)->shouldBeCalled();
        $response = $controller->create($bookingCreateRequest, $manager->reveal());

        $this->assertInstanceOf(Response::class, $response);
    }
}
