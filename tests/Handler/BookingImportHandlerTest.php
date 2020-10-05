<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\Booking\BookingCreate\Experience as BookingCreateExperience;
use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Handler\BookingImportHandler;
use App\Manager\BookingManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\BookingImportHandler
 */
class BookingImportHandlerTest extends TestCase
{
    /**
     * @dataProvider bookingImportRequestProvider
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\Booking\BookingImport\BookingImportRequest
     * @covers \App\Contract\Request\Booking\BookingImport\Guest
     */
    public function testHandleBroadcastRoomPrice(
        BookingImportRequest $bookingImportRequest,
        ?\Throwable $exception = null
    ) {
        $manager = $this->prophesize(BookingManager::class);
        $manager->import(Argument::any())->shouldBeCalled();
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new BookingImportHandler($logger->reveal(), $manager->reveal());

        if ($exception) {
            $this->expectException(get_class($exception));
            $manager->import(Argument::any())->willThrow($exception);
            $logger->error($exception, $bookingImportRequest->getContext())->shouldBeCalled();
        }

        $this->assertNull($handler($bookingImportRequest));
    }

    public function bookingImportRequestProvider()
    {
        $bookingImportRequest = new BookingImportRequest();
        $bookingImportRequest->bookingId = 'SBXFRJBO200101123123';
        $bookingImportRequest->box = '2406';
        $bookingImportRequest->experience = new BookingCreateExperience();
        $bookingImportRequest->experience->id = '3216334';
        $bookingImportRequest->experience->components = [
            'Cup of tea',
            'Una noche muy buena',
        ];
        $bookingImportRequest->currency = 'EUR';
        $bookingImportRequest->voucher = '198257918';
        $bookingImportRequest->startDate = new \DateTime('2020-01-01');
        $bookingImportRequest->endDate = new \DateTime('2020-01-02');
        $bookingImportRequest->customerComment = 'Clean sheets please';
        $bookingImportRequest->guests = [new \App\Contract\Request\Booking\BookingImport\Guest()];
        $bookingImportRequest->guests[0]->firstName = 'Hermano';
        $bookingImportRequest->guests[0]->lastName = 'Guido';
        $bookingImportRequest->guests[0]->email = 'maradona@worldcup.ar';
        $bookingImportRequest->guests[0]->phone = '123 123 123';
        $bookingImportRequest->guests[0]->isPrimary = true;
        $bookingImportRequest->guests[0]->age = 30;
        $bookingImportRequest->guests[0]->country = 'AR';
        yield 'broadcast-happy-days' => [
            clone $bookingImportRequest,
        ];

        yield 'experience-id-not-found' => [
            (function ($bookingImportRequest) {
                $bookingImportRequest->experience->id = '123456789';

                return $bookingImportRequest;
            })(clone $bookingImportRequest),
            new ExperienceNotFoundException(),
        ];
    }
}
