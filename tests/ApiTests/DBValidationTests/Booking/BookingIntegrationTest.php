<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests\Booking;

use App\Constants\DateTimeConstants;
use App\Constraint\AvailabilityTypeConstraint;
use App\Constraint\BookingChannelConstraint;
use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\RoomAvailability;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group booking
 */
class BookingIntegrationTest extends IntegrationTestCase
{
    public const BOX_GOLDEN_ID = '1796';

    private string $experienceGoldenId = '59593';
    private string $componentGoldenId = '213072';
    private string $componentGoldenIdWithDuration2 = '1008863';
    private string $componentGoldenIdInstant = '351306';
    private string $componentGoldenIdOnRequest = '218642';
    private array $componentGoldenIdList = [];
    private static \DateTime $startDate;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$startDate = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
    }

    /**
     * @throws \Exception
     */
    public function prepareData(array $payload): void
    {
        $this->componentGoldenIdList = [
            $this->componentGoldenId,
            $this->componentGoldenIdWithDuration2,
            $this->componentGoldenIdOnRequest,
            $this->componentGoldenIdInstant,
        ];

        self::$bookingHelper->cleanUpBooking(
            $this->componentGoldenIdList,
            [$this->experienceGoldenId]
        );

        self::$bookingHelper->prepareExperience($this->experienceGoldenId);

        self::$bookingHelper->fulfillAvailability(
            $this->componentGoldenIdList,
            $payload
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        self::$bookingHelper->cleanUpBooking(
            $this->componentGoldenIdList,
            [$this->experienceGoldenId],
        );
    }

    public function testBookingCreateUnavailableDatesException(): void
    {
        $this->experienceGoldenId = '59593';
        $this->componentGoldenId = '213072';
        $this->componentGoldenIdWithDuration2 = '1008863';

        $payload['rooms'] = [
            [
                'extraRoom' => false,
                'dates' => [
                    [
                        'day' => (clone self::$startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                        'price' => 0,
                        'extraNight' => false,
                    ],
                    [
                        'day' => (clone self::$startDate)
                            ->modify('+1 day')
                            ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                        'price' => 0,
                        'extraNight' => false,
                    ],
                ],
            ],
        ];

        $payload = self::$bookingHelper->defaultPayload($payload);
        $this->prepareData($payload);
        self::$bookingHelper->setUnavailability($this->componentGoldenIdList, $payload);

        $response = self::$bookingHelper->create($payload);
        self::assertStringContainsString('Unavailable date for booking","code":1300016', $response->getContent());
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @throws \Exception
     */
    public function testCreateDuplicateBooking(): void
    {
        $this->componentGoldenId = '417435';
        $this->experienceGoldenId = '111887';

        $payload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $payload = self::$bookingHelper->defaultPayload($payload);

        $this->prepareData($payload);

        self::$bookingHelper->fulfillAvailability([$this->componentGoldenId], $payload);

        $response = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $response2 = self::$bookingHelper->create($payload);
        self::assertEquals(Response::HTTP_CONFLICT, $response2->getStatusCode());
        self::assertStringContainsString('Resource already exists', $response2->getContent());
    }

    /**
     * @dataProvider dataForCreate
     */
    public function testCreate(array $payload): void
    {
        $this->componentGoldenId = '329655';
        $this->experienceGoldenId = $payload['experience']['id'] !== $this->experienceGoldenId ?
            $payload['experience']['id'] : '140897';

        $payload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $payload = self::$bookingHelper->defaultPayload($payload);

        $this->prepareData($payload);

        $availabilityBeforeBooking = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $response = self::$bookingHelper->create($payload);

        self::assertEmpty($response->getContent());
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $availabilityAfterBooking = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $bookedDates = self::$entityManager->getRepository(BookingDate::class)
            ->findBookingDatesByExperiencesAndDates(
                [$this->experienceGoldenId],
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        foreach ($availabilityAfterBooking as $key => $availability) {
            if (isset($bookedDates[$key]) &&
                $availability['date'] === $bookedDates[$key]['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT)) {
                self::assertEquals(
                        $availabilityBeforeBooking[$key]['usedStock'] + $bookedDates[$key]['usedStock'],
                        $availability['usedStock']
                    );

                self::assertEquals(
                        $availabilityBeforeBooking[$key]['realStock'] - $bookedDates[$key]['usedStock'],
                        $availability['realStock']
                    );

                self::assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
            } else {
                self::assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
                self::assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
                self::assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
            }
        }
    }

    public function dataForCreate(): iterable
    {
        self::setUpBeforeClass();
        self::$startDate = new \DateTime(
            date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))
        );
        $payload = self::$bookingHelper->defaultPayload();

        yield 'happy-path' => [
            'payload' => $payload,
        ];

        yield 'happy-path-with-extra-night' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+2 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);
                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone self::$startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
        ];

        yield 'happy-path-with-extra-room' => [
            (function ($payload) {
                $payload['rooms'] =
                    [
                        [
                            'extraRoom' => false,
                            'dates' => [
                                [
                                    'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                    'price' => 0,
                                    'extraNight' => false,
                                ],
                            ],
                        ],
                        [
                            'extraRoom' => true,
                            'dates' => [
                                [
                                    'day' => (clone self::$startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                    'price' => 5500,
                                    'extraNight' => false,
                                ],
                            ],
                        ],
                    ];

                return $payload;
            })($payload),
        ];

        yield 'happy-path-with-extra-night-and-extra-room' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+2 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
        ];

        yield 'dates-past-the-minimum-booking-duration-should-have-extra-night=true' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+3 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+2 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
        ];

        yield 'happy-path with no availability type' => [
            $payload,
        ];

        yield 'happy-path with instant availability_type' => [
            (function ($payload) {
                $payload['bookingId'] = bin2hex(random_bytes(8));
                $payload['availabilityType'] = AvailabilityTypeConstraint::AVAILABILITY_TYPE_INSTANT;
                $payload['box'] = '2143';
                $payload['experience']['id'] = '179369';

                return $payload;
            })($payload),
        ];

        yield 'happy-path with on-request availability_type' => [
            (function ($payload) {
                $payload['availabilityType'] = AvailabilityTypeConstraint::AVAILABILITY_TYPE_ON_REQUEST;
                $payload['box'] = '2143';
                $payload['experience']['id'] = '55823';

                return $payload;
            })($payload),
        ];
    }

    /**
     * @dataProvider dataForCreateNotHappy
     */
    public function testCreateNotHappy(
        array $payload,
        string $expectedContent,
        callable $extraActions = null
    ): void {
        $this->componentGoldenId = '329655';
        $this->experienceGoldenId = '140897';

        $payload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $payload = self::$bookingHelper->defaultPayload($payload);
        $this->prepareData($payload);

        if ($extraActions) {
            $extraActions($this, $payload, $this->componentGoldenId);
        }

        $availabilityBeforeBooking = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $response = self::$bookingHelper->create($payload);

        self::assertEquals($expectedContent, $response->getContent());
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $availabilityAfterBooking = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        foreach ($availabilityAfterBooking as $key => $availability) {
            self::assertEquals($availabilityBeforeBooking[$key]['usedStock'], $availability['usedStock']);
            self::assertEquals($availabilityBeforeBooking[$key]['realStock'], $availability['realStock']);
            self::assertEquals($availabilityBeforeBooking[$key]['stock'], $availability['stock']);
        }
    }

    public function dataForCreateNotHappy(): iterable
    {
        self::setUpBeforeClass();
        self::$startDate = new \DateTime(
            date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))
        );
        $payload = self::$bookingHelper->defaultPayload();

        yield 'date-not-in-range' => [
            (function ($payload) {
                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+2 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Date out of range","code":1300002}}',
        ];

        yield 'bad-price' => [
            (function ($payload) {
                $payload['rooms'] = [
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => (clone self::$startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Bad price","code":1300001}}',
        ];

        yield 'extra-night-dates-greatest-than-minimum-duration' => [
            (function ($payload) {
                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone self::$startDate)->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+2 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 6500,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+3 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 7500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Date out of range","code":1300002}}',
        ];

        yield 'numbers-of-nights-with-price-0-greatest-than-duration' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+2 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Bad price","code":1300001}}',
        ];

        yield 'more-than-one-extra-room-false' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+2 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"No included room found","code":1300007}}',
        ];

        yield 'rooms-with-different-duration' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+2 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Rooms dont have same duration","code":1300004}}',
        ];

        yield 'rooms-with-same-date' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+2 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Duplicated dates for same room","code":1300008}}',
        ];

        yield 'unallocated-date' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+3 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+2 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Unallocated date","code":1300003}}',
        ];

        yield 'invalid-extra-night' => [
            (function ($payload) {
                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+3 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => self::$startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 10,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+1 day')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+2 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Invalid extra night","code":1300005}}',
        ];

        yield 'not happy-path with other availability_type' => [
            (function ($payload) {
                $payload['availabilityType'] = 'on_request';

                return $payload;
            })($payload),
            '{"error":{"message":"Unprocessable entity","code":1000002,"errors":'.
            '{"availabilityType":["The value you selected is not a valid choice."]}}}',
        ];

        yield 'availability types is not fit with component room-stock-type' => [
            (function ($payload) {
                $payload['availabilityType'] = AvailabilityTypeConstraint::AVAILABILITY_TYPE_ON_REQUEST;

                return $payload;
            })($payload),
            '{"error":{"message":"Unprocessable entity","code":1000002}}',
        ];

        yield 'experience-without-price-and-currency' => [
            self::$bookingHelper->defaultPayload(),
            '{"error":{"message":"Misconfigured experience price","code":1300006}}',
            (function (BookingIntegrationTest $test, $payload) {
                $test::$entityManager
                    ->getConnection()
                    ->executeStatement(
                        "UPDATE experience SET price = 0, currency = null 
                            WHERE golden_id = '".$payload['experience']['id']."'"
                );
            }),
        ];

        yield 'availability-with-stop-sale-date' => [
            (function ($payload) {
                $payload['startDate'] = (clone self::$startDate)
                    ->modify('+12 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['endDate'] = (clone self::$startDate)
                    ->modify('+15 days')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);

                $payload['rooms'] = [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+12 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 10,
                                'extraNight' => false,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+13 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => true,
                            ],
                            [
                                'day' => (clone self::$startDate)
                                    ->modify('+14 days')
                                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                                'price' => 1000,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ];

                return $payload;
            })($payload),
            '{"error":{"message":"Unavailable date for booking","code":1300016}}',
            (function ($test, $payload, $componentGoldenId) {
                $date = (new \DateTime($payload['endDate']))
                    ->modify('-1 day')
                    ->format(DateTimeConstants::DEFAULT_DATE_FORMAT);
                $test::$entityManager->getConnection()
                    ->executeStatement("
                        UPDATE room_availability SET is_stop_sale = true 
                            WHERE component_golden_id = '".$componentGoldenId."'
                            AND date = '".$date."'"
                    );
            }),
        ];
    }

    /**
     * @group update-bit
     */
    public function testUpdateConfirmHappy()
    {
        $this->experienceGoldenId = '89520';
        $overridePayload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $payload = self::$bookingHelper->defaultPayload($overridePayload);
        $this->componentGoldenId = '253214';
        $this->componentGoldenIdWithDuration2 = '417435';

        $this->prepareData($payload);

        $availabilityBeforeBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $patchPayload = $payload;
        $patchPayload['bookingId'] = $payloadUpdate['bookingId'] ?? $payload['bookingId'];
        $patchPayload['voucher'] = $payloadUpdate['voucher'] ?? $payload['voucher'];
        $patchPayload['status'] = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
        $patchPayload['lastStatusChannel'] = $payloadUpdate['lastStatusChannel'] ?? null;

        $response = self::$bookingHelper->update($patchPayload);

        $availabilityAfterBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $endDate = $payload['endDate'];
        foreach ($availabilityBeforeBookingComplete as $key => $formerAvailability) {
            if ($formerAvailability['date'] !== $endDate) {
                self::assertEquals(0, $availabilityAfterBookingComplete[$key]['usedStock']);
                self::assertEquals(
                    $formerAvailability['realStock'] - 1,
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals($formerAvailability['stock'] - 1, $availabilityAfterBookingComplete[$key]['stock']);
            } else {
                self::assertEquals(
                    $formerAvailability['usedStock'], $availabilityAfterBookingComplete[$key]['usedStock']);
                self::assertEquals(
                    $formerAvailability['realStock'],
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals($formerAvailability['stock'], $availabilityAfterBookingComplete[$key]['stock']);
            }
        }
        self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    /**
     * @group update-bit
     */
    public function testUpdateLastStatusChannel()
    {
        $this->experienceGoldenId = '71377';
        $overridePayload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $payload = self::$bookingHelper->defaultPayload($overridePayload);
        $this->componentGoldenId = '262546';

        $this->prepareData($payload);

        $availabilityBeforeBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $patchPayload = $payload;
        $patchPayload['bookingId'] = $payloadUpdate['bookingId'] ?? $payload['bookingId'];
        $patchPayload['voucher'] = $payloadUpdate['voucher'] ?? $payload['voucher'];
        $patchPayload['status'] = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
        $patchPayload['lastStatusChannel'] = BookingChannelConstraint::BOOKING_LAST_STATUS_CHANNEL_PARTNER;

        $response = self::$bookingHelper->update($patchPayload);

        $availabilityAfterBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $endDate = $payload['endDate'];
        $booking = self::$entityManager->getRepository(Booking::class)
            ->findOneBy(['goldenId' => $payload['bookingId']]);
        foreach ($availabilityBeforeBookingComplete as $key => $formerAvailability) {
            if ($formerAvailability['date'] !== $endDate) {
                self::assertEquals(
                    $formerAvailability['usedStock'],
                    $availabilityAfterBookingComplete[$key]['usedStock']
                );
                self::assertEquals(
                    $formerAvailability['realStock'] - 1,
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals(
                    $formerAvailability['stock'] - 1,
                    $availabilityAfterBookingComplete[$key]['stock']
                );
            } else {
                self::assertEquals(
                    $formerAvailability['usedStock'],
                    $availabilityAfterBookingComplete[$key]['usedStock']
                );
                self::assertEquals(
                    $formerAvailability['realStock'],
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals(
                    $formerAvailability['stock'],
                    $availabilityAfterBookingComplete[$key]['stock']
                );
            }
        }
        self::assertEquals($payload['bookingId'], $booking->goldenId);
        self::assertEquals(BookingChannelConstraint::BOOKING_LAST_STATUS_CHANNEL_PARTNER, $booking->lastStatusChannel);
        self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    /**
     * @group update-bit
     */
    public function testUpdateLastStatusChannelInvalid()
    {
        $this->experienceGoldenId = '71377';
        $overridePayload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['3 stays with breakfast'],
        ];
        $payload = self::$bookingHelper->defaultPayload($overridePayload);
        $this->componentGoldenId = '262546';

        $this->prepareData($payload);

        $availabilityBeforeBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $patchPayload = $payload;
        $patchPayload['bookingId'] = $payloadUpdate['bookingId'] ?? $payload['bookingId'];
        $patchPayload['voucher'] = $payloadUpdate['voucher'] ?? $payload['voucher'];
        $patchPayload['status'] = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
        $patchPayload['lastStatusChannel'] = 'whatever';

        $response = self::$bookingHelper->update($patchPayload);

        $availabilityAfterBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $endDate = $payload['endDate'];
        $booking = self::$entityManager->getRepository(Booking::class)
            ->findOneBy(['goldenId' => $payload['bookingId']]);
        foreach ($availabilityBeforeBookingComplete as $key => $formerAvailability) {
            if ($formerAvailability['date'] !== $endDate) {
                self::assertEquals(
                    $formerAvailability['usedStock'] + 1,
                    $availabilityAfterBookingComplete[$key]['usedStock']
                );
                self::assertEquals(
                    $formerAvailability['realStock'] - 1,
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals(
                    $formerAvailability['stock'],
                    $availabilityAfterBookingComplete[$key]['stock']
                );
            } else {
                self::assertEquals(
                    $formerAvailability['usedStock'],
                    $availabilityAfterBookingComplete[$key]['usedStock']
                );
                self::assertEquals(
                    $formerAvailability['realStock'],
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals(
                    $formerAvailability['stock'],
                    $availabilityAfterBookingComplete[$key]['stock']
                );
            }
        }
        self::assertEquals($payload['bookingId'], $booking->goldenId);
        self::assertNull($booking->lastStatusChannel);
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertStringContainsString('lastStatusChannel', $response->getContent());
    }

    /**
     * @group update-bit
     * @dataProvider dataForUpdateBookingUnknownFields
     */
    public function testUpdateBookingUnknownFields(
        array $payloadUpdate,
        int $expectedStatusCode,
        string $expectedMessage,
        string $expectedErrorCode
    ) {
        $this->experienceGoldenId = '70352';
        $overridePayload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $payload = self::$bookingHelper->defaultPayload($overridePayload);
        $this->componentGoldenId = '210442';

        $this->prepareData($payload);

        $availabilityBeforeBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $patchPayload = $payload;
        $patchPayload['bookingId'] = $payloadUpdate['bookingId'] ?? $payload['bookingId'];
        $patchPayload['voucher'] = $payloadUpdate['voucher'] ?? $payload['voucher'];
        $patchPayload['status'] = $payloadUpdate['status'] ?? BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

        $response = self::$bookingHelper->update($patchPayload);

        $availabilityAfterBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $endDate = $payload['endDate'];
        foreach ($availabilityBeforeBookingComplete as $key => $formerAvailability) {
            if ($formerAvailability['date'] !== $endDate) {
                self::assertEquals(1, $availabilityAfterBookingComplete[$key]['usedStock']);
                self::assertEquals(
                    $formerAvailability['realStock'] - 1,
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals($formerAvailability['stock'], $availabilityAfterBookingComplete[$key]['stock']);
            } else {
                self::assertEquals(
                    $formerAvailability['usedStock'],
                    $availabilityAfterBookingComplete[$key]['usedStock']
                );
                self::assertEquals(
                    $formerAvailability['realStock'],
                    $availabilityAfterBookingComplete[$key]['realStock']
                );
                self::assertEquals(
                    $formerAvailability['stock'],
                    $availabilityAfterBookingComplete[$key]['stock']
                );
            }
        }
        self::assertEquals($expectedStatusCode, $response->getStatusCode());
        self::assertStringContainsString($expectedMessage, $response->getContent());
        self::assertStringContainsString($expectedErrorCode, $response->getContent());
    }

    public function dataForUpdateBookingUnknownFields()
    {
        yield 'unknown-status' => [
            ['status' => 'whatever'],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'The value you selected is not a valid choice.',
            '"code":1000002',
        ];

        yield 'unknown-booking' => [
            [
                'bookingId' => 'SBX9876584658',
                'status' => BookingStatusConstraint::BOOKING_STATUS_COMPLETE,
            ],
            Response::HTTP_NOT_FOUND,
            'Booking not found',
            '"code":1000012',
        ];
    }

    /**
     * @group update-bit
     * @dataProvider dataForUpdateBookingUnhappyCases
     */
    public function testUpdateBookingUnhappyCases(
        string $bookingStatus,
        int $expectedStatusCode,
        string $expectedMessage,
        string $expectedErrorCode
    ): void {
        $this->experienceGoldenId = '162433';
        $overridePayload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Coffee with cinnamon'],
        ];
        $overridePayload['availabilityType'] = AvailabilityTypeConstraint::AVAILABILITY_TYPE_INSTANT;
        $payload = self::$bookingHelper->defaultPayload($overridePayload);
        $this->componentGoldenId = '349020';

        $this->prepareData($payload);

        $availabilityBeforeBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $patchPayload = $payload;
        $patchPayload['status'] = $bookingStatus;

        $response = self::$bookingHelper->update($patchPayload);

        $availabilityAfterBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        foreach ($availabilityBeforeBookingComplete as $key => $formerAvailability) {
            self::assertEquals(1, $availabilityAfterBookingComplete[$key]['usedStock']);
            self::assertEquals(
                $formerAvailability['realStock'] - 1,
                $availabilityAfterBookingComplete[$key]['realStock']
            );
            self::assertEquals($formerAvailability['stock'], $availabilityAfterBookingComplete[$key]['stock']);
        }
        self::assertEquals($expectedStatusCode, $response->getStatusCode());
        self::assertStringContainsString($expectedMessage, $response->getContent());
        self::assertStringContainsString($expectedErrorCode, $response->getContent());
    }

    public function dataForUpdateBookingUnhappyCases()
    {
        yield 'unhappy-path-rejected-booking' => [
            'rejected',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Unprocessable entity',
            'code":1000002',
        ];

        yield 'unhappy-path-pending-partner-confirmation-booking' => [
            'pending_partner_confirmation',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Unprocessable entity',
            'code":1000002',
        ];
    }

    /**
     * @group update-bit
     * @dataProvider dataForUpdateBookingHappyCases
     */
    public function testUpdateBookingHappyCases(
        string $bookingStatus,
        int $expectedStatusCode
    ) {
        $this->experienceGoldenId = '144653';
        $overridePayload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Drive a Tesla'],
        ];
        $payload = self::$bookingHelper->defaultPayload($overridePayload);
        $this->componentGoldenId = '367395';

        $this->prepareData($payload);

        $availabilityBeforeBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $patchPayload = $payload;
        $patchPayload['status'] = $bookingStatus;

        $response = self::$bookingHelper->update($patchPayload);

        $availabilityAfterBookingComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        foreach ($availabilityBeforeBookingComplete as $key => $formerAvailability) {
            self::assertEquals(0, $availabilityAfterBookingComplete[$key]['usedStock']);
            self::assertEquals($formerAvailability['realStock'], $availabilityAfterBookingComplete[$key]['realStock']);
            self::assertEquals($formerAvailability['stock'], $availabilityAfterBookingComplete[$key]['stock']);
        }
        self::assertEquals($expectedStatusCode, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    public function dataForUpdateBookingHappyCases()
    {
        yield 'happy-path-rejected-booking' => [
            'rejected',
            Response::HTTP_NO_CONTENT,
        ];

        yield 'happy-path-pending-partner-confirmation-booking' => [
            'pending_partner_confirmation',
            Response::HTTP_NO_CONTENT,
        ];

        yield 'happy-path-cancel-booking' => [
            'cancelled',
            Response::HTTP_NO_CONTENT,
        ];
    }

    /**
     * @group update-bit
     * @dataProvider dataForUpdateBookingExpired
     */
    public function testUpdateBookingAlreadyExpired(array $updatePayload, callable $asserts, bool $haveAvailability = true)
    {
        $this->experienceGoldenId = '101207';
        $this->componentGoldenId = '289884';
        $this->componentGoldenIdWithDuration2 = '1121059';
        $componentIdList = [$this->componentGoldenId, $this->componentGoldenIdWithDuration2];

        $payload['bookingId'] = bin2hex(random_bytes(8));
        $payload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Dinner for 2'],
        ];
        $payload['box'] = '1043346';
        $payload = self::$bookingHelper->defaultPayload($payload);

        self::$bookingHelper->fulfillAvailability($componentIdList, $payload);
        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        self::$entityManager
            ->getConnection()
            ->executeStatement(
                "UPDATE booking SET expired_at = '".
                (new \DateTime('now'))->format(DateTimeConstants::DEFAULT_DATE_TIME_FORMAT).
                "' WHERE golden_id = '".$payload['bookingId']."'")
        ;

        $updatePayload['bookingId'] = $payload['bookingId'];
        $updatePayload['voucher'] = $payload['voucher'];

        if (false === $haveAvailability) {
            self::$bookingHelper->setUnavailability(
                $componentIdList,
                $payload
            );
        }

        $response = self::$bookingHelper->update($updatePayload);

        $asserts($this, $response);
    }

    /**
     * @see ::testUpdateBookingAlreadyExpired
     */
    public function dataForUpdateBookingExpired(): iterable
    {
        yield 'confirm booking expired with availability' => [
            ['status' => 'complete'],
            (function ($test, $response) {
                $test->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
            }),
        ];

        yield 'confirm booking expired without availability' => [
            ['status' => 'complete'],
            (function ($test, $response) {
                $test->assertStringContainsString('Booking has expired', $response->getContent());
                $test->assertStringContainsString('"code":1300010', $response->getContent());
                $test->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
            }),
            false,
        ];

        yield 'cancel booking expired' => [
            ['status' => 'cancelled'],
            (function ($test, $response) {
                $test->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
                $test->assertEmpty($response->getContent());
            }),
        ];
    }

    /**
     * @group update-bit
     */
    public function testUpdateToSameStatusWillFail()
    {
        $this->componentGoldenId = '417435';
        $this->experienceGoldenId = '111887';

        $payload['experience'] = [
            'id' => $this->experienceGoldenId,
            'components' => ['Dinner for 2'],
        ];
        $payload['box'] = '1796';
        $payload = self::$bookingHelper->defaultPayload($payload);

        $this->prepareData($payload);

        $availabilityBeforeComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        $responseCreate = self::$bookingHelper->create($payload);

        self::assertEquals(Response::HTTP_CREATED, $responseCreate->getStatusCode());
        self::assertEmpty($responseCreate->getContent());

        $updatePayload['bookingId'] = $payload['bookingId'];
        $updatePayload['voucher'] = $payload['voucher'];
        $updatePayload['status'] = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

        $responseUpdate = self::$bookingHelper->update($updatePayload);
        self::assertEquals(Response::HTTP_NO_CONTENT, $responseUpdate->getStatusCode());

        $responseUpdate2 = self::$bookingHelper->update($updatePayload);
        self::assertEquals(Response::HTTP_NO_CONTENT, $responseUpdate2->getStatusCode());

        $availabilityAfterComplete = self::$entityManager->getRepository(RoomAvailability::class)
            ->findBookingAvailabilityByExperienceAndDates(
                $this->experienceGoldenId,
                new \DateTime($payload['startDate']),
                new \DateTime($payload['endDate'])
            );

        foreach ($availabilityBeforeComplete as $key => $formerAvailability) {
            if ($formerAvailability['date'] !== $payload['endDate']) {
                self::assertEquals(0, $availabilityAfterComplete[$key]['usedStock']);
                self::assertEquals($formerAvailability['realStock'] - 1, $availabilityAfterComplete[$key]['realStock']);
                self::assertEquals($formerAvailability['stock'] - 1, $availabilityAfterComplete[$key]['stock']);
            } else {
                self::assertEquals($formerAvailability['usedStock'], $availabilityAfterComplete[$key]['usedStock']);
                self::assertEquals($formerAvailability['realStock'], $availabilityAfterComplete[$key]['realStock']);
                self::assertEquals($formerAvailability['stock'], $availabilityAfterComplete[$key]['stock']);
            }
        }
    }
}
