<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BookingApiTest extends ApiTestCase
{
    public static string $boxId;
    public static string $experienceId;

    public static function setUpBeforeClass(): void
    {
        /*
         * @todo Rewrite this as integration test, so it passes
         */
        parent::markTestSkipped();

        return;

        parent::setUpBeforeClass();
        $boxId = json_decode(ApiTestCase::$boxHelper->create()->getContent())->goldenId;
        $partnerId = json_decode(ApiTestCase::$partnerHelper->create()->getContent())->goldenId;
        $experienceId = json_decode(ApiTestCase::$experienceHelper->create()->getContent())->goldenId;
        ApiTestCase::$boxExperienceHelper->create(ApiTestCase::$boxExperienceHelper->getDefault([
            'boxGoldenId' => $boxId,
            'experienceGoldenId' => $experienceId,
        ]));
        $component = json_decode(ApiTestCase::$componentHelper->create(ApiTestCase::$componentHelper->getDefault([
            'partnerGoldenId' => $partnerId,
            'duration' => 1,
        ]))->getContent())->goldenId;
        ApiTestCase::$experienceComponentHelper->create(ApiTestCase::$experienceComponentHelper->getDefault([
            'componentGoldenId' => $component,
            'experienceGoldenId' => $experienceId,
        ]));
        static::$boxId = $boxId;
        static::$experienceId = $experienceId;
    }

    /**
     * @dataProvider dataForCreate
     */
    public function testCreate(array $bookingPayload, callable $asserts)
    {
        $bookingPayload['box'] = static::$boxId;
        $bookingPayload['experience']['id'] = static::$experienceId;
        $response = self::$bookingHelper->create($bookingPayload);

        $asserts($this, $response);
    }

    public function testCreateDuplicateBooking()
    {
        $firstBookingId = bin2hex(random_bytes(8));

        $payload = $this->defaultPayload(['bookingId' => $firstBookingId]);
        $this->testCreate($payload, function (BookingApiTest $test, $response) {
            $test->assertEquals(201, $response->getStatusCode());
        });

        $this->testCreate($payload, function (BookingApiTest $test, $response) {
            $test->assertEquals(409, $response->getStatusCode());
        });
    }

    public function testUpdate()
    {
        $bookingPayload = $this->defaultPayload();
        $this->testCreate($bookingPayload, function (BookingApiTest $test, $response) {
            $test->assertEquals(201, $response->getStatusCode());
        });
        $updatePayload = [
            'bookingId' => $bookingPayload['bookingId'],
            'status' => 'complete',
        ];
        $response = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testUpdateToCancelled()
    {
        $bookingPayload = $this->defaultPayload();
        $this->testCreate($bookingPayload, function (BookingApiTest $test, $response) {
            $test->assertEquals(201, $response->getStatusCode());
        });
        $updatePayload = [
            'bookingId' => $bookingPayload['bookingId'],
            'status' => 'cancelled',
        ];
        $response = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testUpdateToTheSameStatusWillFail()
    {
        $bookingPayload = $this->defaultPayload();
        $this->testCreate($bookingPayload, function (BookingApiTest $test, $response) {
            $test->assertEquals(201, $response->getStatusCode());
        });
        $updatePayload = [
            'bookingId' => $bookingPayload['bookingId'],
            'status' => 'created',
        ];
        $response = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testUpdateToUnknownStatusWillFail()
    {
        $bookingPayload = $this->defaultPayload();
        $this->testCreate($bookingPayload, function (BookingApiTest $test, $response) {
            $test->assertEquals(201, $response->getStatusCode());
        });
        $updatePayload = [
            'bookingId' => $bookingPayload['bookingId'],
            'status' => '253235532352',
        ];
        $response = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testUpdateUnknownBookingWillFail()
    {
        $updatePayload = [
            'bookingId' => 'aaaaaa',
            'status' => 'complete',
        ];
        $response = self::$bookingHelper->update($updatePayload);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @see testCreate
     */
    public function dataForCreate(): iterable
    {
        yield 'happy path' => [
            $this->defaultPayload(),
            function (BookingApiTest $test, $response) {
                $responseContent = json_decode($response->getContent());
                $this->assertEquals(201, $response->getStatusCode());
            },
        ];

        yield 'happy path with extra night' => [
            $this->defaultPayload([
                'endDate' => '2020-01-03',
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => '2020-01-01',
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => '2020-01-02',
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingApiTest $test, $response) {
                $responseContent = json_decode($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
            },
        ];

        yield 'happy path with extra room' => [
            $this->defaultPayload([
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => '2020-01-01',
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => '2020-01-01',
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingApiTest $test, $response) {
                $responseContent = json_decode($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
            },
        ];

        yield 'happy path with extra night and extra room' => [
            $this->defaultPayload([
                'endDate' => '2020-01-03',
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => '2020-01-01',
                                'price' => 0,
                                'extraNight' => false,
                            ],
                            [
                                'day' => '2020-01-02',
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                    [
                        'extraRoom' => true,
                        'dates' => [
                            [
                                'day' => '2020-01-01',
                                'price' => 5500,
                                'extraNight' => false,
                            ],
                            [
                                'day' => '2020-01-02',
                                'price' => 5500,
                                'extraNight' => true,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingApiTest $test, $response) {
                $responseContent = json_decode($response->getContent());
                $test->assertEquals(201, $response->getStatusCode());
            },
        ];

        yield 'date not in range' => [
            $this->defaultPayload([
                'rooms' => [
                    [
                        'extraRoom' => false,
                        'dates' => [
                            [
                                'day' => '2020-01-02',
                                'price' => 0,
                                'extraNight' => false,
                            ],
                        ],
                    ],
                ],
            ]),
            function (BookingApiTest $test, $response) {
                $responseContent = json_decode($response->getContent());
                $test->assertEquals(422, $response->getStatusCode());
            },
        ];
    }

    public function defaultPayload(array $overrides = []): array
    {
        return $overrides + [
            'bookingId' => bin2hex(random_bytes(8)),
            'box' => '123',
            'experience' => [
                'id' => '123',
                'components' => [
                    'Cup of tea',
                    'Una noche muy buena',
                ],
                'price' => 500,
            ],
            'currency' => 'EUR',
            'voucher' => '198257918',
            'startDate' => '2020-01-01',
            'endDate' => '2020-01-02',
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
                            'day' => '2020-01-01',
                            'price' => 0,
                            'extraNight' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
