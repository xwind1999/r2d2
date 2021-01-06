<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\QuickData;

use App\Constants\AvailabilityConstants;
use App\Constants\DateTimeConstants;
use App\Entity\RoomAvailability;
use App\Helper\AvailabilityHelper;
use App\Repository\BookingDateRepository;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ApiTests\IntegrationTestCase;

/**
 * @group quickdata
 */
class QuickDataIntegrationTest extends IntegrationTestCase
{
    /**
     * @dataProvider getPackageProvider
     */
    public function testGetPackageV1(
        string $componentGoldenId,
        string $experienceGoldenId,
        \DateTime $dateFrom,
        \DateTime $dateTo,
        array $payload,
        array $expectedResult,
        ?callable $bookingCreate = null
    ) {
        static::cleanUp();
        self::$bookingHelper->cleanUpBooking(
            [$componentGoldenId],
            [$experienceGoldenId],
            self::$container->get('doctrine.orm.entity_manager')
        );

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-availability-list', 30);
        $this->consume('listener-room-availability', 30);

        if ($bookingCreate) {
            $bookingCreate();
        }

        $response = self::$quickDataHelper->getPackage(
            $experienceGoldenId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));

        static::cleanUp();
        self::$bookingHelper->cleanUpBooking(
            [$componentGoldenId],
            [$experienceGoldenId],
            self::$container->get('doctrine.orm.entity_manager')
        );
    }

    public function getPackageProvider()
    {
        $experienceId = '7307';
        $componentId = '227914';

        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+2 month');
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $payload = [
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => (clone $dateFrom)->modify('+1 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+1 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => (clone $dateFrom)->modify('+2 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => true,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+2 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => (clone $dateFrom)->modify('+3 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+3 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => (clone $dateFrom)->modify('+4 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+4 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => (clone $dateFrom)->modify('+5 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'quantity' => '0',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+5 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => (clone $dateFrom)->modify('+6 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'quantity' => '1',
            ],
        ];

        yield 'get-package-v1' => [
            $componentId,
            $experienceId,
            $dateFrom,
            (function ($dateTo) {
                return $dateTo->modify('+350 day');
            })(clone $dateTo),
            $payload,
            (function ($componentId) {
                $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

                $expectedResult = [
                    'ListPrestation' => [[
                        'Availabilities' => ['1', '0', '1', '1', '0', '1'],
                        'PrestId' => 1,
                        'Duration' => $component->duration,
                        'LiheId' => 1,
                        'PartnerCode' => '00037411',
                        'ExtraNight' => false,
                        'ExtraRoom' => false,
                    ]],
                ];

                for ($i = 0; $i < 350; ++$i) {
                    $expectedResult['ListPrestation'][0]['Availabilities'][] = '0';
                }

                return $expectedResult;
            })($componentId),
        ];

        yield 'get-package-v1-with-booking-creation' => [
            '213072',
            '59593',
            $dateFrom,
            $dateTo,
            (function ($payload) {
                $newPayload = [];
                foreach ($payload as $item) {
                    $newPayload[] = array_replace_recursive($item, ['product' => ['id' => '213072']]);
                }

                return $newPayload;
            })($payload),
            (function ($componentId) {
                $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

                $expectedResult = [
                    'ListPrestation' => [[
                        'Availabilities' => ['0', '0', '1', '1', '0', '1'],
                        'PrestId' => 1,
                        'Duration' => $component->duration,
                        'LiheId' => 1,
                        'PartnerCode' => '00030786',
                        'ExtraNight' => false,
                        'ExtraRoom' => false,
                    ]],
                ];

                return $expectedResult;
            })($componentId),
            (function () use ($dateFrom) {
                $payloadOverride = [
                    'startDate' => $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    'endDate' => (clone $dateFrom)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                ];

                $payloadBooking = self::$bookingHelper->defaultPayload($payloadOverride);
                $response = self::$bookingHelper->create($payloadBooking);
                self::assertEquals(201, $response->getStatusCode());
            }),
        ];
    }

    public function testGetPackageWithStopSale()
    {
        static::cleanUp();

        $experienceId = '122476';
        $componentId = '326541';
        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+ 1 month');
        $dateTo = (clone $dateFrom)->modify('+2 day');
        $payload = [
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+2 day')->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => true,
                'quantity' => '2',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+2 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '0',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-availability-list', 30);
        $this->consume('listener-room-availability', 30);

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => [1, 0, 0],
                'PrestId' => 1,
                'Duration' => $component->duration,
                'LiheId' => 1,
                'PartnerCode' => '00147276',
                'ExtraNight' => true,
                'ExtraRoom' => true,
            ]],
        ];

        $response = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageWithOnRequestStopSale()
    {
        static::cleanUp();

        $experienceId = '45618';
        $componentId = '225702';

        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+2 month');
        $dateTo = (clone $dateFrom)->modify('+2 day');
        $payload = [
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+2 day')->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => true,
                'quantity' => '2',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+2 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '0',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-availability-list', 30);
        $this->consume('listener-room-availability', 30);

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => ['r', '0', 'r'],
                'PrestId' => 1,
                'Duration' => $component->duration,
                'LiheId' => 1,
                'PartnerCode' => '00040406',
                'ExtraNight' => true,
                'ExtraRoom' => true,
            ]],
        ];

        for ($i = 0; $i < 351; ++$i) {
            $expectedResult['ListPrestation'][0]['Availabilities'][] = 'r';
        }

        $response = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->modify('+351 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageWithOnRequestDefaultAvailability()
    {
        static::cleanUp();

        $experienceId = '78034';
        $componentId = '249910';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+2 day');

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => ['r', 'r', 'r'],
                'PrestId' => 1,
                'Duration' => $component->duration,
                'LiheId' => 1,
                'PartnerCode' => '00142022',
                'ExtraNight' => false,
                'ExtraRoom' => false,
            ]],
        ];

        $response = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageWithInactiveRelationshipBetweenComponents()
    {
        static::cleanUp();

        $experienceId = '140255';
        $componentId = '295263';

        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+3 months');
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $payload = [
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+2 day')->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+2 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+4 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => true,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+4 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+5 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '0',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+5 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+6 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $componentId = '227915';
        $payload = [
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+2 day')->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+2 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+4 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => true,
                'quantity' => '1',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+4 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+5 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '0',
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+5 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+6 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'quantity' => '1',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-availability-list', 30);
        $this->consume('listener-room-availability', 30);

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => ['1', '1', '1', '0', '0', '1'],
                'PrestId' => 1,
                'Duration' => $component->duration,
                'LiheId' => 1,
                'PartnerCode' => '00258222',
                'ExtraNight' => true,
                'ExtraRoom' => true,
            ]],
        ];

        $response = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageWithInvalidExperienceId()
    {
        static::cleanUp();

        $experienceId = '7306';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $expectedResult = [
            'ResponseStatus' => [
                'ErrorCode' => 'NotFoundException',
                'Message' => 'Resource not found',
                'StackTrace' => '',
                'Errors' => [],
            ],
        ];

        $response = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageWithoutAvailability()
    {
        static::cleanUp();

        $experienceId = '7307';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of last month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => [
                    '0',
                    '0',
                    '0',
                    '0',
                    '0',
                    '0',
                ],
                'PrestId' => 1,
                'Duration' => 1,
                'LiheId' => 1,
                'PartnerCode' => '00037411',
                'ExtraNight' => false,
                'ExtraRoom' => false,
            ]],
        ];

        $response = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageStockZero()
    {
        static::cleanUp();

        $experienceId = '57277';
        $componentId = '269119';

        $this->generateAvailability($componentId);

        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+6 months');
        $dateTo = (clone $dateFrom)->modify('+5 days');

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($componentId);

        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => [
                    '1',
                    '1',
                    '0',
                    '1',
                    '0',
                    '1',
                ],
                'PrestId' => 1,
                'Duration' => $component->duration,
                'LiheId' => 1,
                'PartnerCode' => '00257938',
                'ExtraNight' => true,
                'ExtraRoom' => true,
            ]],
        ];

        $responseWithStockZero = self::$quickDataHelper->getPackage(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $this->assertEquals($expectedResult, json_decode($responseWithStockZero->getContent(), true));
    }

    public function testGetPackageV2()
    {
        static::cleanUp();

        $this->consume('event-calculate-flat-manageable-component', 100);

        $experienceIds = ['2611', '7307'];
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));

        $expectedResults = [];
        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container->get(RoomAvailabilityRepository::class)->findAvailableRoomsByMultipleExperienceIds($experienceIds, $dateFrom);
        foreach ($roomAvailabilities as $availability) {
            $expectedResults[] = [
                'PackageCode' => (int) $availability['experience_golden_id'],
                'ListPrestation' => [[
                    'Availabilities' => ['1'],
                    'PrestId' => 1,
                    'Duration' => $availability['duration'],
                    'LiheId' => 1,
                    'PartnerCode' => $availability['partner_golden_id'],
                    'ExtraNight' => (bool) $availability['is_sellable'],
                    'ExtraRoom' => (bool) $availability['is_sellable'],
                ]],
            ];
        }

        $expected = [
            'ListPackage' => $expectedResults,
        ];

        $response = json_decode(
            self::$quickDataHelper->getPackageV2(
                implode(',', $experienceIds),
                $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
            )->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        usort($response['ListPackage'], function ($current, $next) {
            return $current['PackageCode'] > $next['PackageCode'];
        });

        $this->assertEquals($expected, $response);
    }

    public function testGetPackageV2WithStockZero()
    {
        static::cleanUp();

        $experienceId1 = '59593';
        $componentId1 = '213072';
        $experienceId2 = '103492';
        $componentId2 = '282687';

        $this->generateAvailability($componentId1);
        $this->generateAvailability($componentId2);

        $this->consume('event-calculate-flat-manageable-component', 100);

        $experienceIds = [$experienceId1, $experienceId2];
        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+6 months');

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsByMultipleExperienceIds($experienceIds, $dateFrom);

        $expectedResults = [];
        foreach ($roomAvailabilities as $availability) {
            $expectedResults[] = [
                'PackageCode' => (int) $availability['experience_golden_id'],
                'ListPrestation' => [[
                    'Availabilities' => ['1'],
                    'PrestId' => 1,
                    'Duration' => $availability['duration'],
                    'LiheId' => 1,
                    'PartnerCode' => $availability['partner_golden_id'],
                    'ExtraNight' => (bool) $availability['is_sellable'],
                    'ExtraRoom' => (bool) $availability['is_sellable'],
                ]],
            ];
        }

        $expected = [
            'ListPackage' => $expectedResults,
        ];
        usort($expected['ListPackage'], function ($current, $next) {
            return $current['PackageCode'] > $next['PackageCode'];
        });
        // Getting the first response with availability with stock zero in db
        $response = json_decode(
            self::$quickDataHelper->getPackageV2(
                implode(',', $experienceIds),
                $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
            )->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        usort($response['ListPackage'], function ($current, $next) {
            return $current['PackageCode'] > $next['PackageCode'];
        });

        $this->assertEquals($expected, $response);
    }

    /**
     * @throws \JsonException
     *                        Obs: The getPackageV2 is used only on JB OOB flow which only use the instant booking.
     *                        It means that even with on_request availability on db these data must not return.
     */
    public function testGetPackageV2WithOnRequest()
    {
        static::cleanUp();

        $experienceId1 = '55823';
        $componentId1 = '218642';

        $experienceId2 = '70373';
        $componentId2 = '322730';

        $experienceId3 = '78034';
        $componentId3 = '249910';

        $this->generateAvailability($componentId1);
        $this->generateAvailability($componentId2);
        $this->generateAvailability($componentId3);

        $this->consume('event-calculate-flat-manageable-component', 100);

        $experienceIds = [$experienceId1, $experienceId2, $experienceId3];
        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+6 months');

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsByMultipleExperienceIds($experienceIds, $dateFrom);

        $expectedResults = [];
        foreach ($roomAvailabilities as $availability) {
            $expectedResults[] = [
                'PackageCode' => (int) $availability['experience_golden_id'],
                'ListPrestation' => [[
                    'Availabilities' => ['1'],
                    'PrestId' => 1,
                    'Duration' => $availability['duration'],
                    'LiheId' => 1,
                    'PartnerCode' => $availability['partner_golden_id'],
                    'ExtraNight' => (bool) $availability['is_sellable'],
                    'ExtraRoom' => (bool) $availability['is_sellable'],
                ]],
            ];
        }

        $expected = [
            'ListPackage' => $expectedResults,
        ];
        usort($expected['ListPackage'], function ($current, $next) {
            return $current['PackageCode'] > $next['PackageCode'];
        });
        // Getting the first response with availability with stock zero in db
        $response = json_decode(
            self::$quickDataHelper->getPackageV2(
                implode(',', $experienceIds),
                $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
            )->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        usort($response['ListPackage'], function ($current, $next) {
            return $current['PackageCode'] > $next['PackageCode'];
        });

        $this->assertEquals($expected, $response);
    }

    /**
     * @throws \Exception
     */
    public function testAvailabilityPricePeriod(): void
    {
        static::cleanUp();

        $experienceId = '7307';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');
        $datePeriod = new \DatePeriod($dateFrom, new \DateInterval('P1D'), $dateTo);

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container
            ->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsAndPricesByExperienceIdAndDates($experienceId, $dateFrom, $dateTo)
        ;

        $resultArray = [];
        foreach ($roomAvailabilities as $availability) {
            $availability['price'] = !empty($availability['price']) ? (int) $availability['price'] / 100 : 0;
            $result = [
                'Date' => (new \DateTime($availability['date']))->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                'AvailabilityValue' => $availability['stock'],
                'SellingPrice' => $availability['price'],
                'BuyingPrice' => $availability['price'],
            ];

            if ('1' === $availability['isStopSale']) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                ];
            } elseif ('stock' === $availability['roomStockType'] && $availability['stock'] > 0) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_AVAILABLE,
                ];
            } elseif ('on_request' === $availability['roomStockType']) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_REQUEST,
                ];
            } else {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                    'AvailabilityValue' => 0,
                ];
            }
            $resultArray[$availability['date']] = $result;
        }

        foreach ($datePeriod as $date) {
            if (!isset($resultArray[$date->format('Y-m-d')])) {
                $resultArray[$date->format('Y-m-d')] = [
                    'Date' => $date->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                    'AvailabilityValue' => 0,
                    'SellingPrice' => 0,
                    'BuyingPrice' => 0,
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                ];
            }
        }

        usort($resultArray, function ($a, $b) {
            return $a['Date'] <=> $b['Date'];
        });

        $expectedResult = [
            'DaysAvailabilityPrice' => $resultArray,
        ];

        $response = self::$quickDataHelper->availabilityPricePeriod(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    /**
     * @throws \Exception
     */
    public function testAvailabilityPricePeriodWithBooking(): void
    {
        static::cleanUp();
        $experienceId = '59593';
        $entityManager = self::$container->get('doctrine.orm.entity_manager');

        self::$bookingHelper->cleanUpBooking(
            ['213072'],
            [$experienceId],
            $entityManager);

        self::$bookingHelper->fulfillAvailability(['213072'], $entityManager);

        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');
        $datePeriod = new \DatePeriod($dateFrom, new \DateInterval('P1D'), $dateTo);

        // start booking process
        $payloadOverride = [
            'startDate' => $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            'endDate' => (clone $dateFrom)->modify('+1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
        ];

        $payloadBooking = self::$bookingHelper->defaultPayload($payloadOverride);
        $response = self::$bookingHelper->create($payloadBooking);
        self::assertEquals(201, $response->getStatusCode());
        // ending booking process

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container
            ->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsAndPricesByExperienceIdAndDates($experienceId, $dateFrom, $dateTo);

        $bookingDates = self::$container
            ->get(BookingDateRepository::class)
            ->findBookingDatesByExperiencesAndDates([$experienceId], $dateFrom, $dateTo);

        $roomAvailabilities = self::$container
            ->get(AvailabilityHelper::class)
            ->getRealStock($roomAvailabilities, $bookingDates);

        $resultArray = [];
        foreach ($roomAvailabilities as $availability) {
            $availability['price'] = !empty($availability['price']) ? (int) $availability['price'] / 100 : 0;
            $result = [
                'Date' => (new \DateTime($availability['date']))->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                'AvailabilityValue' => $availability['stock'],
                'SellingPrice' => $availability['price'],
                'BuyingPrice' => $availability['price'],
            ];

            if ('1' === $availability['isStopSale']) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                ];
            } elseif ('stock' === $availability['roomStockType'] && $availability['stock'] > 0) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_AVAILABLE,
                ];
            } elseif ('on_request' === $availability['roomStockType']) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_REQUEST,
                ];
            } else {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                    'AvailabilityValue' => 0,
                ];
            }
            $resultArray[$availability['date']] = $result;
        }

        foreach ($datePeriod as $date) {
            if (!isset($resultArray[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)])) {
                $resultArray[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)] = [
                    'Date' => $date->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                    'AvailabilityValue' => 0,
                    'SellingPrice' => 0,
                    'BuyingPrice' => 0,
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                ];
            }
        }

        usort($resultArray, function ($a, $b) {
            return $a['Date'] <=> $b['Date'];
        });

        $expectedResult = [
            'DaysAvailabilityPrice' => $resultArray,
        ];

        $response = self::$quickDataHelper->availabilityPricePeriod(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));

        self::$bookingHelper->cleanUpBooking(
            ['213072'],
            [$experienceId],
            self::$container->get('doctrine.orm.entity_manager'));
    }

    /**
     * @throws \Exception
     */
    public function testAvailabilityPricePeriodForRequestWithPricesAndNoAvailability(): void
    {
        static::cleanUp();

        $experienceId = '70373';
        $componentId = '322730';

        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+5 month');
        $dateTo = (clone $dateFrom)->modify('+5 day');
        $datePeriod = new \DatePeriod($dateFrom, new \DateInterval('P1D'), (clone $dateTo)->modify('+1 day'));
        $payload = [
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'price' => [
                    'amount' => 21.34,
                    'currencyCode' => 'EUR',
                ],
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+2 day')->modify('+1 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'price' => [
                    'amount' => 21.34,
                    'currencyCode' => 'EUR',
                ],
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+2 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'price' => [
                    'amount' => 21.34,
                    'currencyCode' => 'EUR',
                ],
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+3 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+4 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'price' => [
                    'amount' => 21.34,
                    'currencyCode' => 'EUR',
                ],
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+4 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+5 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'price' => [
                    'amount' => 21.34,
                    'currencyCode' => 'EUR',
                ],
            ],
            [
                'product' => [
                    'id' => $componentId,
                ],
                'dateFrom' => (clone $dateFrom)->modify('+5 day')->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => (clone $dateFrom)->modify('+6 day')->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\TH:i:s.uP'),
                'price' => [
                    'amount' => 21.34,
                    'currencyCode' => 'EUR',
                ],
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomPrice($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-price-list', 30);
        $this->consume('listener-room-price', 30);

        $resultArray = [];
        foreach ($datePeriod as $date) {
            $resultArray[] = [
                'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_REQUEST,
                'Date' => $date->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                'AvailabilityValue' => '1',
                'SellingPrice' => '21.34',
                'BuyingPrice' => '21.34',
            ];
        }

        $expectedResult = [
            'DaysAvailabilityPrice' => $resultArray,
        ];

        $response = self::$quickDataHelper->availabilityPricePeriod(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    /**
     * @throws \Exception
     */
    public function testGetRangeV2()
    {
        static::cleanUp();

        $boxId = '851518';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));

        $avs = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsByBoxIdWithUpdatedQuery($boxId, $dateFrom);

        $data = [];
        foreach ($avs as $comp => $avs2) {
            $d = [
                'Package' => $avs2['experienceGoldenId'],
                'Stock' => 0,
                'Request' => 0,
            ];
            if ('on_request' === $avs2['roomStockType']) {
                $d['Request'] = 1;
            } else {
                $d['Stock'] = 1;
            }
            $data[] = $d;
        }

        $expectedResult = [
            'PackagesList' => $data,
        ];

        usort($expectedResult['PackagesList'], function ($current, $next) {
            return $current['Package'] > $next['Package'];
        });

        $response = json_decode(
            self::$quickDataHelper->getRangeV2(
                $boxId, $dateFrom->format(
                    DateTimeConstants::DEFAULT_DATE_FORMAT
                )
            )->getContent(), true);

        usort($response['PackagesList'], function ($current, $next) {
            return $current['Package'] > $next['Package'];
        });

        $this->assertEquals($expectedResult, $response);
    }

    public function testAvailabilityPricePeriodStockZero(): void
    {
        static::cleanUp();

        $experienceId = '59593';
        $componentId = '213072';

        $this->generateAvailability($componentId);

        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+6 months');
        $dateTo = (clone $dateFrom)->modify('+5 day');
        $datePeriod = new \DatePeriod($dateFrom, new \DateInterval('P1D'), $dateTo);

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container
            ->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsAndPricesByExperienceIdAndDates($experienceId, $dateFrom, $dateTo)
        ;

        $resultArray = [];
        foreach ($roomAvailabilities as $availability) {
            $availability['price'] = !empty($availability['price']) ? (int) $availability['price'] / 100 : 0;
            $result = [
                'Date' => (new \DateTime($availability['date']))->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                'AvailabilityValue' => $availability['stock'],
                'SellingPrice' => $availability['price'],
                'BuyingPrice' => $availability['price'],
            ];

            if ('1' === $availability['isStopSale']) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                ];
            } elseif ('stock' === $availability['roomStockType'] && $availability['stock'] > 0) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_AVAILABLE,
                ];
            } elseif ('on_request' === $availability['roomStockType']) {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_REQUEST,
                ];
            } else {
                $result += [
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                    'AvailabilityValue' => 0,
                ];
            }
            $resultArray[$availability['date']] = $result;
        }

        foreach ($datePeriod as $date) {
            if (!isset($resultArray[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)])) {
                $resultArray[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)] = [
                    'Date' => $date->format(DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT),
                    'AvailabilityValue' => 0,
                    'SellingPrice' => 0,
                    'BuyingPrice' => 0,
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                ];
            }
        }

        usort($resultArray, function ($a, $b) {
            return $a['Date'] <=> $b['Date'];
        });

        $expectedResult = [
            'DaysAvailabilityPrice' => $resultArray,
        ];

        // Getting the first response with availability with stock zero in db
        $responseWithStockZero = self::$quickDataHelper->availabilityPricePeriod(
            $experienceId,
            $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $this->assertEquals($expectedResult, json_decode($responseWithStockZero->getContent(), true));
    }

    private function generateAvailability(string $componentId)
    {
        $dateFrom = (new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month'))))
            ->modify('+6 months');

        $payload =
            [
                [
                    'product' => [
                        'id' => $componentId,
                    ],
                    'dateFrom' => (clone $dateFrom)->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'dateTo' => (clone $dateFrom)->modify('+1 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'isStopSale' => false,
                    'quantity' => '1',
                ],
                [
                    'product' => [
                        'id' => $componentId,
                    ],
                    'dateFrom' => (clone $dateFrom)->modify('+1 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'dateTo' => (clone $dateFrom)->modify('+2 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'isStopSale' => false,
                    'quantity' => '2',
                ],
                [
                    'product' => [
                        'id' => $componentId,
                    ],
                    'dateFrom' => (clone $dateFrom)->modify('+3 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'dateTo' => (clone $dateFrom)->modify('+4 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'isStopSale' => false,
                    'quantity' => '5',
                ],
                [
                    'product' => [
                        'id' => $componentId,
                    ],
                    'dateFrom' => (clone $dateFrom)->modify('+4 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'dateTo' => (clone $dateFrom)->modify('+5 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'isStopSale' => true,
                    'quantity' => '0',
                ],
                [
                    'product' => [
                        'id' => $componentId,
                    ],
                    'dateFrom' => (clone $dateFrom)->modify('+5 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'dateTo' => (clone $dateFrom)->modify('+6 day')->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'updatedAt' => (new \DateTime())->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                    'isStopSale' => false,
                    'quantity' => '2',
                ],
            ];

        self::$broadcastListenerHelper->testRoomAvailability($payload);

        $this->consume('listener-room-availability-list', 30);
        $this->consume('listener-room-availability', 30);
    }
}
