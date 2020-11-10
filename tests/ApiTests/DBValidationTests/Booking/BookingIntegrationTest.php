<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests\Booking;

use App\Constants\DateTimeConstants;
use App\Constraint\BookingStatusConstraint;
use App\Entity\BookingDate;
use App\Entity\RoomAvailability;
use App\Tests\ApiTests\IntegrationTestCase;

/**
 * @group booking
 */
class BookingIntegrationTest extends IntegrationTestCase
{
    private const BOX_GOLDEN_ID = '1796';
    private const EXPERIENCE_GOLDEN_ID = '59593';

    private $entityManager;
    private \DateTime $startDate;
    private string $componentGoldenId;

    public function setup(): void
    {
        $this->componentGoldenId = '213072';
        static::cleanUp();
        $this->entityManager = self::$container->get('doctrine.orm.entity_manager');
        $this->cleanUpBooking();
        $this->prepareExperience();
        $this->startDate = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $this->fulfillAvailability($this->defaultPayload());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpBooking();
    }

    /**
     * @beforeClass
     */
    public function cleanUpBooking(): void
    {
        $this->entityManager->getConnection()->executeStatement('SET foreign_key_checks = 0');
        $this->entityManager->getConnection()
            ->executeStatement("DELETE FROM r2d2.booking_date WHERE component_golden_id = '".$this->componentGoldenId."'");
        $this->entityManager->getConnection()
            ->executeStatement('DELETE FROM r2d2.booking WHERE experience_golden_id = \''.self::EXPERIENCE_GOLDEN_ID.'\'');
        $this->entityManager->getConnection()->executeStatement('SET foreign_key_checks = 1')
        ;
    }

    /**
     * @beforeClass
     */
    private function fulfillAvailability(array $payload = [])
    {
        $payload = $this->defaultPayload($payload);
        $this->entityManager
            ->getConnection()
            ->executeStatement("UPDATE room_availability SET stock = 50 
                    WHERE component_golden_id = '".$this->componentGoldenId."' AND date BETWEEN 
                    '".$payload['startDate']."' AND '".$payload['endDate']."'")
        ;
    }

    private function prepareExperience()
    {
        $this->entityManager
            ->getConnection()
            ->executeStatement("UPDATE experience SET price = 500, currency = 'EUR' 
                    WHERE golden_id = '".self::EXPERIENCE_GOLDEN_ID."'")
        ;
    }

    private function setUnavailability(array $payload = [])
    {
        $payload = $this->defaultPayload($payload);
        $this->entityManager->getConnection()
            ->executeStatement("UPDATE room_availability SET stock = 0 WHERE component_golden_id = '".$this->componentGoldenId."'
                AND date = '".$payload['startDate']."'")
        ;
    }

    public function defaultPayload(array $overrides = []): array
    {
        return $overrides +
            [
                'bookingId' => bin2hex(random_bytes(8)),
                'box' => self::BOX_GOLDEN_ID,
                'experience' => [
                    'id' => self::EXPERIENCE_GOLDEN_ID,
                    'components' => [
                        'Three night stay',
                    ],
                ],
                'currency' => 'EUR',
                'voucher' => '198257918',
                'startDate' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'endDate' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'customerComment' => 'Clean sheets please',
                'guests' => [
                    [
                        'firstName' => 'Hermano',
                        'lastName' => 'Guido',
                        'email' => 'maradona@worldcup.ar',
                        'phone' => '123 123 123',
                    ],
                ],
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ];
    }

    public function testBookingCreateUnavailableDatesException()
    {
        $payload = $this->defaultPayload();
        $this->setUnavailability();

        $payload['rooms'] = [
            [
                'extraRoom' => false,
                'dates' => [
                    [
                        'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                        'price' => 0,
                        'extraNight' => false,
                    ],
                    [
                        'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                        'price' => 0,
                        'extraNight' => false,
                    ],
                ],
            ],
        ];

        $response = self::$bookingHelper->create($payload);
        $this->assertStringContainsString('Unavailable date for booking","code":1300016', $response->getContent());
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateDuplicateBooking()
    {
        $this->fulfillAvailability();
        $payload = $this->defaultPayload();
        $payload['bookingId'] = bin2hex(random_bytes(8));

        $response = self::$bookingHelper->create($payload);

        $this->assertEquals(201, $response->getStatusCode());

        $response2 = self::$bookingHelper->create($payload);
        $this->assertEquals(409, $response2->getStatusCode());
        $this->assertStringContainsString('Resource already exists', $response2->getContent());
    }

    /**
     * @dataProvider defaultDataForCreate
     */
    public function testCreate(array $payload, callable $asserts, callable $extraActions = null)
    {
        if ($extraActions) {
            $extraActions($this, $payload, $this->componentGoldenId);
        }

        $this->fulfillAvailability($payload);
        $availabilityBeforeBooking = $this->entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                self::EXPERIENCE_GOLDEN_ID,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $response = self::$bookingHelper->create($payload);

        $availabilityAfterBooking = $this->entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                self::EXPERIENCE_GOLDEN_ID,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $bookedDates = $this->entityManager->getRepository(BookingDate::class)
            ->findBookingDatesByExperiencesAndDates(
                [self::EXPERIENCE_GOLDEN_ID],
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $asserts($this, $response, $availabilityBeforeBooking, $availabilityAfterBooking, $bookedDates);
    }

    public function defaultDataForCreate(): iterable
    {
        $this->startDate = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        yield 'happy-path' => [
            $this->defaultPayload(),
            function (
                BookingIntegrationTest $test,
                $response,
                $availabilityBeforeBooking,
                $availabilityAfterBooking,
                $bookedDates
            ) {
                $test->assertEmpty($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    if (isset($bookedDates[$key]) && $availability['date'] === $bookedDates[$key]['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT)) {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'] + $bookedDates[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'] - $bookedDates[$key]['usedStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    } else {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    }
                }
            },
        ];

        yield 'happy-path-with-extra-night' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (
                BookingIntegrationTest $test,
                $response,
                $availabilityBeforeBooking,
                $availabilityAfterBooking,
                $bookedDates
            ) {
                $test->assertEmpty($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    if (isset($bookedDates[$key]) && $availability['date'] === $bookedDates[$key]['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT)) {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'] + $bookedDates[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'] - $bookedDates[$key]['usedStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    } else {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    }
                }
            },
        ];

        yield 'happy-path-with-extra-room' => [
            $this->defaultPayload([
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (
                BookingIntegrationTest $test,
                $response,
                $availabilityBeforeBooking,
                $availabilityAfterBooking,
                $bookedDates
            ) {
                $test->assertEmpty($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    if (isset($bookedDates[$key]) && $availability['date'] === $bookedDates[$key]['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT)) {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'] + $bookedDates[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'] - $bookedDates[$key]['usedStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    } else {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    }
                }
            },
        ];

        yield 'happy-path-with-extra-night-and-extra-room' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (
                BookingIntegrationTest $test,
                $response,
                $availabilityBeforeBooking,
                $availabilityAfterBooking,
                $bookedDates
            ) {
                $test->assertEmpty($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    if (isset($bookedDates[$key]) && $availability['date'] === $bookedDates[$key]['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT)) {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'] + $bookedDates[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'] - $bookedDates[$key]['usedStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    } else {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    }
                }
            },
        ];

        yield 'date-not-in-range' => [
            $this->defaultPayload([
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Date out of range","code":1300002}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'bad-price' => [
            $this->defaultPayload([
                'rooms' => [
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Bad price","code":1300001}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'extra-night-dates-greatest-than-minimum-duration' => [
            $this->defaultPayload([
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 6500,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+3 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 7500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Date out of range","code":1300002}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'numbers-of-nights-with-price-0-greatest-than-duration' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Bad price","code":1300001}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'dates-past-the-minimum-booking-duration-should-have-extra-night=true' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+3 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (
                BookingIntegrationTest $test,
                $response,
                $availabilityBeforeBooking,
                $availabilityAfterBooking,
                $bookedDates
            ) {
                $test->assertEmpty($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    if (isset($bookedDates[$key]) && $availability['date'] === $bookedDates[$key]['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT)) {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'] + $bookedDates[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'] - $bookedDates[$key]['usedStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    } else {
                        $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                        $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                    }
                }
            },
        ];

        yield 'more-than-one-extra-room-false' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"No included room found","code":1300007}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'rooms-with-different-duration' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Rooms dont have same duration","code":1300004}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'rooms-with-same-date' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Duplicated dates for same room","code":1300008}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'unallocated-date' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+3 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Unallocated date","code":1300003}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'invalid-extra-night' => [
            $this->defaultPayload([
                'endDate' => (clone $this->startDate)->modify('+3 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => $this->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 10,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+2 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals('{"error":{"message":"Invalid extra night","code":1300005}}', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
        ];

        yield 'experience-without-price-and-currency' => [
            $this->defaultPayload(),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals(
                    '{"error":{"message":"The experience price must be a number greater than zero","code":1300006}}',
                    $response->getContent()
                );
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
            (function ($test) {
                $test->entityManager
                    ->getConnection()
                    ->executeStatement("UPDATE experience SET price = 0, currency = null 
                    WHERE golden_id = '".self::EXPERIENCE_GOLDEN_ID."'")
                ;
            }),
        ];

        yield 'availability-with-stop-sale-date' => [
            $this->defaultPayload([
                'startDate' => (clone $this->startDate)->modify('+12 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'endDate' => (clone $this->startDate)->modify('+15 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone $this->startDate)->modify('+12 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 10,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+13 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone $this->startDate)->modify('+14 days')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingIntegrationTest $test, $response, $availabilityBeforeBooking, $availabilityAfterBooking) {
                $test->assertEquals(
                    '{"error":{"message":"Unavailable date for booking","code":1300016}}',
                    $response->getContent()
                );
                $test->assertEquals(422, $response->getStatusCode());
                foreach ($availabilityAfterBooking as $key => $availability) {
                    $this->assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                    $this->assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
                }
            },
            (function ($test, $payload, $componentGoldenId) {
                $date = (new \DateTime($payload['endDate']))->modify('-1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT);
                $test->entityManager->getConnection()
                    ->executeStatement("
                        UPDATE room_availability SET is_stop_sale = true 
                            WHERE component_golden_id = '".$componentGoldenId."'
                            AND date = '".$date."'"
                    );
            }),
        ];
    }

    /**
     * @dataProvider dataForUpdate
     */
    public function testUpdate(array $updatePayload, callable $asserts)
    {
        $payload = $this->defaultPayload();
        $this->fulfillAvailability();

        $availabilityBeforeBookingComplete = $this->entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                self::EXPERIENCE_GOLDEN_ID,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        $this->assertEquals(201, $responseCreate->getStatusCode());
        $this->assertEmpty($responseCreate->getContent());

        $updatePayload['bookingId'] = $updatePayload['bookingId'] ?? $payload['bookingId'];
        $updatePayload['voucher'] = $payload['voucher'];
        $updatePayload['status'] = $updatePayload['status'] ?? BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

        $response = self::$bookingHelper->update($updatePayload);

        $availabilityAfterBookingComplete = $this->entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                self::EXPERIENCE_GOLDEN_ID,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $asserts($this, $response, $availabilityBeforeBookingComplete, $availabilityAfterBookingComplete, $payload['endDate']);
    }

    public function dataForUpdate()
    {
        yield 'happy-path-confirm-booking' => [
            ['status' => 'complete'],
            (function ($test, $response, $availabilityBeforeComplete, $availabilityAfterComplete, $endDate) {
                foreach ($availabilityBeforeComplete as $key => $formerAvailability) {
                    if ($formerAvailability['date'] !== $endDate) {
                        $this->assertEquals(0, $availabilityAfterComplete[$key]['usedStock']);
                        $this->assertEquals($formerAvailability['realStock'] - 1, $availabilityAfterComplete[$key]['realStock']);
                        $this->assertEquals($formerAvailability['stock'] - 1, $availabilityAfterComplete[$key]['stock']);
                    } else {
                        $this->assertEquals($formerAvailability['usedStock'], $availabilityAfterComplete[$key]['usedStock']);
                        $this->assertEquals($formerAvailability['realStock'], $availabilityAfterComplete[$key]['realStock']);
                        $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
                    }
                }
                $test->assertEquals(204, $response->getStatusCode());
                $test->assertEmpty($response->getContent());
            }),
        ];

        yield 'happy-path-cancel-booking' => [
            ['status' => 'cancelled'],
            (function ($test, $response, $availabilityBeforeComplete, $availabilityAfterComplete) {
                foreach ($availabilityBeforeComplete as $key => $formerAvailability) {
                    $this->assertEquals(0, $availabilityAfterComplete[$key]['usedStock']);
                    $this->assertEquals($formerAvailability['realStock'], $availabilityAfterComplete[$key]['realStock']);
                    $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
                }
                $test->assertEquals(204, $response->getStatusCode());
                $test->assertEmpty($response->getContent());
            }),
        ];

        yield 'unknown-status' => [
            ['status' => 'whatever'],
            (function ($test, $response, $availabilityBeforeComplete, $availabilityAfterComplete, $endDate) {
                foreach ($availabilityBeforeComplete as $key => $formerAvailability) {
                    if ($formerAvailability['date'] !== $endDate) {
                        $this->assertEquals(1, $availabilityAfterComplete[$key]['usedStock']);
                        $this->assertEquals($formerAvailability['realStock'] - 1, $availabilityAfterComplete[$key]['realStock']);
                        $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
                    } else {
                        $this->assertEquals($formerAvailability['usedStock'], $availabilityAfterComplete[$key]['usedStock']);
                        $this->assertEquals($formerAvailability['realStock'], $availabilityAfterComplete[$key]['realStock']);
                        $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
                    }
                }
                $test->assertEquals(422, $response->getStatusCode());
                $test->assertStringContainsString('The value you selected is not a valid choice.', $response->getContent());
                $test->assertStringContainsString('"code":1000002', $response->getContent());
            }),
        ];

        yield 'unknown-booking' => [
            ['bookingId' => 'SBX9876584658'],
            (function ($test, $response, $availabilityBeforeComplete, $availabilityAfterComplete, $endDate) {
                foreach ($availabilityBeforeComplete as $key => $formerAvailability) {
                    if ($formerAvailability['date'] !== $endDate) {
                        $this->assertEquals(1, $availabilityAfterComplete[$key]['usedStock']);
                        $this->assertEquals($formerAvailability['realStock'] - 1, $availabilityAfterComplete[$key]['realStock']);
                        $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
                    } else {
                        $this->assertEquals($formerAvailability['usedStock'], $availabilityAfterComplete[$key]['usedStock']);
                        $this->assertEquals($formerAvailability['realStock'], $availabilityAfterComplete[$key]['realStock']);
                        $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
                    }
                }
                $test->assertEquals(404, $response->getStatusCode());
                $test->assertStringContainsString('Booking not found', $response->getContent());
                $test->assertStringContainsString('"code":1000012', $response->getContent());
            }),
        ];
    }

    /**
     * @dataProvider dataForUpdateBookingExpired
     */
    public function testUpdateBookingAlreadyExpired(array $updatePayload, callable $asserts, bool $haveAvailability = true)
    {
        $this->fulfillAvailability();
        $payload = $this->defaultPayload();
        $responseCreate = self::$bookingHelper->create($payload);

        $this->assertEquals(201, $responseCreate->getStatusCode());
        $this->assertEmpty($responseCreate->getContent());

        static::$container
            ->get('doctrine.orm.entity_manager')
            ->getConnection()
            ->executeStatement("UPDATE booking SET expired_at = '".(new \DateTime('now'))->format('Y-m-d H:i:s').
                "' WHERE golden_id = '".$payload['bookingId']."'")
        ;

        $updatePayload['bookingId'] = $payload['bookingId'];
        $updatePayload['voucher'] = $payload['voucher'];

        if (false === $haveAvailability) {
            $this->setUnavailability();
        }

        $response = self::$bookingHelper->update($updatePayload);

        $asserts($this, $response);
    }

    public function dataForUpdateBookingExpired()
    {
        yield 'confirm booking expired with availability' => [
            ['status' => 'complete'],
            (function ($test, $response) {
                $test->assertEquals(204, $response->getStatusCode());
            }),
        ];

        yield 'confirm booking expired without availability' => [
            ['status' => 'complete'],
            (function ($test, $response) {
                $test->assertStringContainsString('Booking has expired', $response->getContent());
                $test->assertStringContainsString('"code":1300010', $response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
            }),
            false,
        ];

        yield 'cancel booking expired' => [
            ['status' => 'cancelled'],
            (function ($test, $response) {
                $test->assertEquals(204, $response->getStatusCode());
                $test->assertEmpty($response->getContent());
            }),
        ];
    }

    public function testUpdateToSameStatusWillFail()
    {
        $payload = $this->defaultPayload();
        $this->fulfillAvailability();

        $availabilityBeforeComplete = $this->entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                self::EXPERIENCE_GOLDEN_ID,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $responseCreate = self::$bookingHelper->create($payload);

        $this->assertEquals(201, $responseCreate->getStatusCode());
        $this->assertEmpty($responseCreate->getContent());

        $updatePayload['bookingId'] = $payload['bookingId'];
        $updatePayload['voucher'] = $payload['voucher'];
        $updatePayload['status'] = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

        $responseUpdate = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(204, $responseUpdate->getStatusCode());

        $responseUpdate2 = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(204, $responseUpdate2->getStatusCode());

        $availabilityAfterComplete = $this->entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                self::EXPERIENCE_GOLDEN_ID,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        foreach ($availabilityBeforeComplete as $key => $formerAvailability) {
            if ($formerAvailability['date'] !== $payload['endDate']) {
                $this->assertEquals(0, $availabilityAfterComplete[$key]['usedStock']);
                $this->assertEquals($formerAvailability['realStock'] - 1, $availabilityAfterComplete[$key]['realStock']);
                $this->assertEquals($formerAvailability['stock'] - 1, $availabilityAfterComplete[$key]['stock']);
            } else {
                $this->assertEquals($formerAvailability['usedStock'], $availabilityAfterComplete[$key]['usedStock']);
                $this->assertEquals($formerAvailability['realStock'], $availabilityAfterComplete[$key]['realStock']);
                $this->assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
            }
        }
    }
}
