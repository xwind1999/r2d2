<?php

declare(strict_types=1);

namespace App\Command\Import;

use App\Contract\Request\Booking\BookingCreate\Experience;
use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use App\Contract\Request\Booking\BookingImport\Guest;
use App\Contract\Request\Booking\BookingImport\Room;
use App\Contract\Request\Booking\BookingImport\RoomDate as RoomDateImport;

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

            $record['components'] = str_replace('0x', '', $record['components']);
            $record['components'] = hex2bin($record['components']);
            $record['components'] = gzdecode($record['components']);
            $record['components'] = str_replace("\0", '', $record['components']);
            $record['components'] = json_decode($record['components'], true, 512, JSON_THROW_ON_ERROR);
            $bookingCreateRequest->experience->components = $record['components']['components'][0];

            $record['customerData'] = str_replace('0x', '', $record['customerData']);
            $record['customerData'] = hex2bin($record['customerData']);
            $record['customerData'] = gzdecode($record['customerData']);
            $record['customerData'] = str_replace("\0", '', $record['customerData']);
            $record['customerData'] = json_decode($record['customerData'], true, 512, JSON_THROW_ON_ERROR);

            $guest = new Guest();
            $guest->firstName = !empty($record['customerData']['firstname']) ? $record['customerData']['firstname'] : 'firstName';
            $guest->lastName = !empty($record['customerData']['lastname']) ? $record['customerData']['lastname'] : 'lastName';
            $guest->email = !empty($record['customerData']['email']) ? $record['customerData']['email'] : 'email@email.com';
            $guest->phone = !empty($record['customerData']['telephone']) ? $record['customerData']['telephone'] : '3311111111';
            $bookingCreateRequest->guests = [$guest];

            $roomTypeArray = !empty($record['roomType']) ? explode(';', $record['roomType']) : [];
            $roomPriceArray = !empty($record['roomPrice']) ? explode(';', $record['roomPrice']) : [];
            $roomBeginDatesArray = !empty($record['beginRoomDate']) ? explode(';', $record['beginRoomDate']) : [];
            $roomEndDatesArray = !empty($record['endRoomDate']) ? explode(';', $record['endRoomDate']) : [];

            $roomDateIndex = 0;
            $roomDatesArray = [];
            $room = new Room();

            if (empty($roomTypeArray) || empty($roomBeginDatesArray) || empty($roomEndDatesArray)) {
                $room->extraRoom = false;
                $datePeriod = new \DatePeriod(
                    new \DateTime($record['arrivalDate']),
                    new \DateInterval('P1D'),
                    new \DateTime($record['endDate'])
                );

                $bookingDatePerNightPrice = (int) $record['experiencePrice'] / iterator_count($datePeriod);
                foreach ($datePeriod as $date) {
                    $roomDate = new RoomDateImport();
                    $roomDate->price = (int) $bookingDatePerNightPrice;
                    $roomDate->day = $date;
                    $roomDate->extraNight = false;
                    $roomDatesArray[$roomDateIndex] = $roomDate;
                    ++$roomDateIndex;
                }

                $room->dates = $roomDatesArray;
                $bookingCreateRequest->rooms = [$room];
            } else {
                foreach ($roomTypeArray as $index => $roomType) {
                    if (self::JARVIS_BOOKING_EXTRA_NIGHT_STATUS === $roomType) {
                        $room->extraRoom = false;
                        $datePeriod = new \DatePeriod(
                            new \DateTime($record['arrivalDate']),
                            new \DateInterval('P1D'),
                            new \DateTime($record['endDate'])
                        );

                        $extraNightPerDayPrice = (int) $roomPriceArray[$index] / iterator_count($datePeriod);
                        $bookingDatePerNightPrice = (int) $record['experiencePrice'] / iterator_count($datePeriod);
                        $extraNightStartDate = new \DateTime($roomBeginDatesArray[$index]);
                        $extraNightEndDate = new \DateTime($roomEndDatesArray[$index]);

                        foreach ($datePeriod as $date) {
                            $roomDate = new RoomDateImport();
                            $roomDate->day = $date;

                            if ($date >= $extraNightStartDate && $date < $extraNightEndDate) { // if it is extra night
                                $roomDate->price = (int) $extraNightPerDayPrice;
                                $roomDate->extraNight = true;
                            } else {
                                $roomDate->price = (int) $bookingDatePerNightPrice;
                                $roomDate->extraNight = false;
                            }

                            $roomDatesArray[$roomDateIndex] = $roomDate;
                            ++$roomDateIndex;
                        }

                        $room->dates = $roomDatesArray;
                        $bookingCreateRequest->rooms = [$room];
                    } else {
                        $datePeriod = new \DatePeriod(
                            new \DateTime($roomBeginDatesArray[$index]),
                            new \DateInterval('P1D'),
                            new \DateTime($roomEndDatesArray[$index])
                        );

                        $bookingDatePerNightPrice = (int) $record['experiencePrice'] / iterator_count($datePeriod);

                        foreach ($datePeriod as $date) {
                            $roomDate = new RoomDateImport();
                            $roomDate->price = (int) $bookingDatePerNightPrice;
                            $roomDate->day = $date;
                            $roomDate->extraNight = false;
                            $roomDatesArray[$roomDateIndex] = $roomDate;
                            ++$roomDateIndex;
                        }
                        $room->dates = $roomDatesArray;

                        $extraRoom = new Room();
                        $extraRoom->extraRoom = true;
                        $extraRoom->dates = $roomDatesArray;

                        $bookingCreateRequest->rooms = [$room, $extraRoom];
                    }
                }
            }

            $errors = $this->validator->validate($bookingCreateRequest);
            if ($errors->count() > 0) {
                $this->logError($errors);
            }

            $this->messageBus->dispatch($bookingCreateRequest);
        }
    }
}
