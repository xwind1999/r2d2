<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\Booking\BookingCreate\Experience;
use App\Contract\Request\Booking\BookingCreate\Room;
use App\Contract\Request\Booking\BookingCreate\RoomDate;
use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use App\Contract\Request\Booking\BookingImport\Guest;

class BookingImportCommand extends AbstractImportCommand
{
    protected static $defaultName = 'r2d2:booking:import';
    protected const JARVIS_BOOKING_EXTRA_NIGHT_STATUS = 'extra_night';
    protected const IMPORT_FIELDS = [
        'goldenId',
        'boxId',
        'experienceId',
        'experiencePrice',
        'currency',
        'voucher',
        'arrivalDate',
        'endDate',
        'additionalComment',
        'customerData',
        'components',
        'roomPrice',
        'roomType',
        'beginRoomDate',
        'endRoomDate',
    ];

    protected function process(\Iterator $records): void
    {
        foreach ($records as $record) {
            $bookingCreateRequest = new BookingImportRequest();
            $bookingCreateRequest->bookingId = $record['goldenId'];
            $bookingCreateRequest->box = $record['boxId'];
            $bookingCreateRequest->currency = $record['currency'];
            $bookingCreateRequest->voucher = $record['voucher'];
            $bookingCreateRequest->startDate = new \DateTime($record['arrivalDate']);
            $bookingCreateRequest->endDate = new \DateTime($record['endDate']);
            $bookingCreateRequest->customerComment = $record['additionalComment'] ?: null;

            $bookingCreateRequest->experience = new Experience();
            $bookingCreateRequest->experience->id = $record['experienceId'];
            $record['components'] = json_decode($record['components'], true, 512, JSON_THROW_ON_ERROR);
            $bookingCreateRequest->experience->components = $record['components']['components'][0];
            $bookingCreateRequest->guests = [
                $this->serializer->deserialize($record['customerData'], Guest::class, 'json'),
            ];

            $roomTypeArray = explode(';', $record['roomType']);
            $roomPriceArray = explode(';', $record['roomPrice']);
            $roomBeginDatesArray = explode(';', $record['beginRoomDate']);
            $roomEndDatesArray = explode(';', $record['endRoomDate']);

            $roomDateIndex = 0;
            $roomDatesArray = [];
            $room = new Room();
            $room->extraRoom = false; // it doesnt affect the flow, we dont need to care about it during the import

            foreach ($roomTypeArray as $index => $roomType) {
                $datePeriod = new \DatePeriod(
                    new \DateTime($roomBeginDatesArray[$index]),
                    new \DateInterval('P1D'),
                    (new \DateTime($roomEndDatesArray[$index]))
                );

                if (self::JARVIS_BOOKING_EXTRA_NIGHT_STATUS === $roomType) {
                    $extraNightPerDayPrice = (int) $roomPriceArray[$index] / iterator_count($datePeriod);

                    foreach ($datePeriod as $key => $date) {
                        $roomDate = new RoomDate();
                        $roomDate->price = (int) $extraNightPerDayPrice;
                        $roomDate->day = $date;
                        $roomDate->extraNight = true;
                        $roomDatesArray[$roomDateIndex] = $roomDate;
                        ++$roomDateIndex;
                    }
                } else {
                    $bookingDatePerNightPrice = (int) $record['experiencePrice'] / iterator_count($datePeriod);
                    foreach ($datePeriod as $key => $date) {
                        $roomDate = new RoomDate();
                        $roomDate->price = (int) $bookingDatePerNightPrice;
                        $roomDate->day = $date;
                        $roomDate->extraNight = false;
                        $roomDatesArray[$roomDateIndex] = $roomDate;
                        ++$roomDateIndex;
                    }
                }
            }

            $room->dates = $roomDatesArray;
            $bookingCreateRequest->rooms = [$room];

            $errors = $this->validator->validate($bookingCreateRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($bookingCreateRequest);
        }
    }
}
