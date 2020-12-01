<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Constants\AvailabilityConstants;
use App\Constants\DateTimeConstants;
use App\Helper\AvailabilityHelper;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Helper\AvailabilityHelper
 */
class AvailabilityHelperTest extends ProphecyTestCase
{
    /**
     * @covers ::convertAvailabilityTypeToExplicitQuickdataValue
     */
    public function testConvertAvailableValueToRequest(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $this->assertEquals(
            'Unavailable',
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                0,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                0,
                '1'
            )
        );
        $this->assertEquals(
            AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_AVAILABLE,
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                1,
                '0'
            )
        );
        $this->assertEquals(
            AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                1,
                '1'
            )
        );
        $this->assertEquals(
            AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_REQUEST,
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                0,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                0,
                '1'
            )
        );
        $this->assertEquals(
            'Request',
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                1,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            $availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                1,
                '1'
            )
        );
    }

    /**
     * @covers ::getRoomStockShortType
     *
     * @dataProvider roomStockType
     */
    public function testGetRoomStockShortType(string $inputString, string $expectedString): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $this->assertEquals($expectedString, $availabilityHelper->getRoomStockShortType($inputString));
    }

    /**
     * @covers ::buildDataForGetPackage
     */
    public function testBuildDataForGetPackageV2(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $availabilities = ['1', '1', '1'];
        $duration = 1;
        $partnerId = '1234';
        $isSellable = false;

        $returnArray = [
            'Availabilities' => $availabilities,
            'PrestId' => 1,
            'Duration' => $duration,
            'LiheId' => 1,
            'PartnerCode' => $partnerId,
            'ExtraNight' => $isSellable,
            'ExtraRoom' => $isSellable,
        ];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->buildDataForGetPackage(
                $availabilities,
                $duration,
                $partnerId,
                $isSellable,
            )
        );
    }

    /**
     * @covers ::fillMissingAvailabilityForGetPackage
     * @covers ::getRoomStockShortType
     * @covers ::validateStockType
     * @covers ::validateMissingAvailability
     */
    public function testBuildDataForGetPackage(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $dateFrom = new \DateTime('2020-10-01');
        $dateTo = new \DateTime('2020-10-03');
        $roomStockType = 'stock';
        $duration = 1;
        $partnerCode = '1234';
        $isSellable = false;
        $availabilities = [
            '2020-10-01' => [
                'date' => '2020-10-01',
                'roomStockType' => 'stock',
                'stock' => '1',
                'isStopSale' => 0,
                'duration' => '1',
                'isSellable' => '0',
                'partnerGoldenId' => '1234',
            ],
            '2020-10-02' => [
                'date' => '2020-10-02',
                'roomStockType' => 'stock',
                'stock' => '1',
                'isStopSale' => 0,
                'duration' => '1',
                'isSellable' => '0',
                'partnerGoldenId' => '1234',
            ],
            '2020-10-03' => [
                'date' => '2020-10-03',
                'roomStockType' => 'stock',
                'stock' => '0',
                'isStopSale' => 0,
                'duration' => '1',
                'isSellable' => '0',
                'partnerGoldenId' => '1234',
            ],
        ];

        $returnArray = [
            'Availabilities' => ['1', '1', '0'],
            'PrestId' => 1,
            'Duration' => 1,
            'LiheId' => 1,
            'PartnerCode' => '1234',
            'ExtraNight' => false,
            'ExtraRoom' => false,
        ];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->fillMissingAvailabilityForGetPackage(
                $availabilities,
                $roomStockType,
                $duration,
                $partnerCode,
                $isSellable,
                $dateFrom,
                $dateTo
            )
        );
    }

    /**
     * @covers ::fillMissingAvailabilityForGetPackage
     * @covers ::getRoomStockShortType
     * @covers ::validateStockType
     * @covers ::validateMissingAvailability
     */
    public function testBuildDataForGetPackageWithMissingAvailabilitiesOnRequest(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $dateFrom = new \DateTime('2020-10-01');
        $dateTo = new \DateTime('2020-10-03');
        $roomStockType = 'on_request';
        $duration = 1;
        $partnerCode = '1234';
        $isSellable = false;
        $availabilities = [
            '2020-10-01' => [
                'date' => '2020-10-01',
                'type' => 'on_request',
                'stock' => '1',
                'isStopSale' => 1,
                'duration' => '1',
                'isSellable' => '0',
                'partnerGoldenId' => '1234',
            ],
            '2020-10-03' => [
                'date' => '2020-10-03',
                'type' => 'on_request',
                'stock' => '1',
                'isStopSale' => 0,
                'duration' => '1',
                'isSellable' => '0',
                'partnerGoldenId' => '1234',
            ],
        ];

        $returnArray = [
            'Availabilities' => ['0', 'r', 'r'],
            'PrestId' => 1,
            'Duration' => 1,
            'LiheId' => 1,
            'PartnerCode' => '1234',
            'ExtraNight' => false,
            'ExtraRoom' => false,
        ];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->fillMissingAvailabilityForGetPackage(
                $availabilities,
                $roomStockType,
                $duration,
                $partnerCode,
                $isSellable,
                $dateFrom,
                $dateTo
            )
        );
    }

    /**
     * @covers ::fillMissingAvailabilityForGetPackage
     * @covers ::getRoomStockShortType
     * @covers ::validateStockType
     * @covers ::validateMissingAvailability
     */
    public function testBuildDataForGetPackageWithMissingAvailabilities(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $dateFrom = new \DateTime('2020-10-01');
        $dateTo = new \DateTime('2020-10-02');
        $roomStockType = 'stock';
        $duration = 1;
        $partnerCode = '1234';
        $isSellable = false;
        $availabilities = [
            '2020-10-01' => [
                'date' => '2020-10-01',
                'type' => 'stock',
                'stock' => '1',
                'isStopSale' => 0,
                'duration' => '1',
                'isSellable' => '0',
                'partnerGoldenId' => '1234',
            ],
        ];

        $returnArray = [
            'Availabilities' => ['1', '0'],
            'PrestId' => 1,
            'Duration' => 1,
            'LiheId' => 1,
            'PartnerCode' => '1234',
            'ExtraNight' => false,
            'ExtraRoom' => false,
        ];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->fillMissingAvailabilityForGetPackage(
                $availabilities,
                $roomStockType,
                $duration,
                $partnerCode,
                $isSellable,
                $dateFrom,
                $dateTo
            )
        );
    }

    /**
     * @covers ::buildDataForGetRange
     */
    public function testBuildDataForGetRange(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $availabilities = [
            [
                'roomStockType' => 'stock',
                'experienceGoldenId' => '1234',
            ],
            [
                'roomStockType' => 'on_request',
                'experienceGoldenId' => '1235',
            ],
            [
                'roomStockType' => 'allotment',
                'experienceGoldenId' => '1236',
            ],
        ];

        $returnArray = [
            [
                'Package' => '1234',
                'Stock' => 1,
                'Request' => 0,
            ],
            [
                'Package' => '1235',
                'Stock' => 0,
                'Request' => 1,
            ],
            [
                'Package' => '1236',
                'Stock' => 1,
                'Request' => 0,
            ],
        ];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->buildDataForGetRange($availabilities)
        );
    }

    /**
     * @covers ::convertToShortType
     */
    public function testConvertToShortTypeInstant(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $stockList = ['1', '2', '3', '0', '1', '0', '5', '1'];
        $returnArray = ['1', '1', '1', '0', '1', '0', '1', '1'];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->convertToShortType(
                $stockList,
                '1'
            )
        );
    }

    /**
     * @covers ::convertToShortType
     */
    public function testConvertToShortTypeOnRequest(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $stockList = ['1', '2', '3', '0', '1', '0', '5', '1'];
        $returnArray = ['r', 'r', 'r', '0', 'r', '0', 'r', 'r'];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->convertToShortType(
                $stockList,
                'r'
            )
        );
    }

    /**
     * @covers ::fillMissingAvailabilitiesForAvailabilityPrice
     */
    public function testFillMissingAvailabilities(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $availabilities = [
            '2020-06-20T00:00:00.000000' => [
                'Date' => '2020-06-20T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-21T00:00:00.000000' => [
                'Date' => '2020-06-21T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-23T00:00:00.000000' => [
                'Date' => '2020-06-23T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
        ];

        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $returnArray = [
            '2020-06-20T00:00:00.000000' => [
                'Date' => '2020-06-20T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-21T00:00:00.000000' => [
                'Date' => '2020-06-21T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-22T00:00:00.000000' => [
                'Date' => '2020-06-22T00:00:00.000000',
                'AvailabilityValue' => 0,
                'AvailabilityStatus' => 'Unavailable',
                'SellingPrice' => 0.00,
                'BuyingPrice' => 0.00,
            ],
            '2020-06-23T00:00:00.000000' => [
                'Date' => '2020-06-23T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-24T00:00:00.000000' => [
                'Date' => '2020-06-24T00:00:00.000000',
                'AvailabilityValue' => 0,
                'AvailabilityStatus' => 'Unavailable',
                'SellingPrice' => 0.00,
                'BuyingPrice' => 0.00,
            ],
            '2020-06-25T00:00:00.000000' => [
                'Date' => '2020-06-25T00:00:00.000000',
                'AvailabilityValue' => 0,
                'AvailabilityStatus' => 'Unavailable',
                'SellingPrice' => 0.00,
                'BuyingPrice' => 0.00,
            ],
        ];

        $this->assertEquals(
            $returnArray,
            $availabilityHelper->fillMissingAvailabilitiesForAvailabilityPrice(
                $availabilities,
                $dateFrom,
                $dateTo,
                null
            )
        );
    }

    /**
     * @covers ::fillMissingAvailabilitiesForAvailabilityPrice
     */
    public function testFillMissingAvailabilitiesWithEnoughDate(): void
    {
        $availabilityHelper = new AvailabilityHelper();

        $availabilities = [
            '2020-06-20T00:00:00.000000' => [
                'Date' => '2020-06-20T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-21T00:00:00.000000' => [
                'Date' => '2020-06-21T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
            '2020-06-22T00:00:00.000000' => [
                'Date' => '2020-06-22T00:00:00.000000',
                'AvailabilityValue' => 1,
                'AvailabilityStatus' => 'Available',
                'SellingPrice' => 86.45,
                'BuyingPrice' => 86.45,
            ],
        ];

        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-22');

        $this->assertEquals(
            $availabilities,
            $availabilityHelper->fillMissingAvailabilitiesForAvailabilityPrice(
                $availabilities,
                $dateFrom,
                $dateTo,
                null
            )
        );
    }

    public function roomStockType(): array
    {
        return [
            [
                'inputString' => 'allotment',
                'expectedString' => '1',
            ],
            [
                'inputString' => 'stock',
                'expectedString' => '1',
            ],
            [
                'inputString' => 'on_request',
                'expectedString' => 'r',
            ],
            [
                'inputString' => 'any-thing-else',
                'expectedString' => '0',
            ],
        ];
    }

    /**
     * @dataProvider bookingAvailabilityProvider
     * @covers ::getRealStock
     */
    public function testGetRoomAvailabilityRealStock(array $availabilities, array $bookingStockDate, callable $asserts)
    {
        $availabilityHelper = new AvailabilityHelper();
        $usedAvailabilities = $availabilityHelper->getRealStock($availabilities, $bookingStockDate);

        $asserts($this, $usedAvailabilities, $bookingStockDate, $availabilities);
    }

    public function bookingAvailabilityProvider()
    {
        $componentGoldenId = '12345';
        $experienceGoldenId = '54321';
        $datetime = new \DateTime('2020-10-01');

        $availabilities = [
            $datetime->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => [
                'stock' => 10,
                'date' => $datetime,
                'type' => 'stock',
                'componentGoldenId' => $componentGoldenId,
            ],
            '2020-10-02' => [
                'stock' => 0,
                'date' => (clone $datetime)->modify('+1 day'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
            ],
            '2020-10-03' => [
                'stock' => 10,
                'date' => (clone $datetime)->modify('+2 days'),
                'type' => 'on_request',
                'componentGoldenId' => '1111',
            ],
        ];

        $bookingStockDate = [
            [
                'componentGoldenId' => $componentGoldenId,
                'experienceGoldenId' => $experienceGoldenId,
                'date' => $datetime,
                'usedStock' => '4',
            ],
        ];

        yield 'booking-availability-calculate' => [
            $availabilities,
            $bookingStockDate,
            (function ($test, $usedAvailabilities, $bookingStock, $roomAvailabilities) {
                foreach ($usedAvailabilities as $date => $usedStock) {
                    foreach ($bookingStock as $booking) {
                        if ($booking['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT) === $date) {
                            $realStock = max($roomAvailabilities[$date]['stock'] - $booking['usedStock'], 0);
                            $test->assertEquals(
                                $usedStock['stock'],
                                $realStock
                            );
                        }
                    }
                }
            }),
        ];

        yield 'multiple-booking-availability-calculate' => [
            $availabilities,
            (function ($componentGoldenId, $datetime) {
                $bookingDates = [
                    [
                        'componentGoldenId' => $componentGoldenId,
                        'date' => $datetime,
                        'usedStock' => '4',
                    ],
                    [
                        'componentGoldenId' => $componentGoldenId,
                        'date' => (clone $datetime)->modify('+1 day'),
                        'usedStock' => '1',
                    ],
                    [
                        'componentGoldenId' => $componentGoldenId,
                        'date' => (clone $datetime)->modify('+2 days'),
                        'usedStock' => '2',
                    ],
                ];

                return $bookingDates;
            })($componentGoldenId, $datetime),
            (function ($test, $usedAvailabilities, $bookingStock, $roomAvailabilities) {
                foreach ($usedAvailabilities as $date => $usedStock) {
                    foreach ($bookingStock as $booking) {
                        if ($booking['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT) === $date &&
                            $usedStock['componentGoldenId'] === $booking['componentGoldenId']) {
                            $realStock = max($roomAvailabilities[$date]['stock'] - $booking['usedStock'], 0);
                            $test->assertEquals(
                                $usedStock['stock'],
                                $realStock
                            );
                        }
                    }
                }
            }),
        ];

        yield 'multiple-booking-availability-with-experience' => [
            [
                $datetime->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => [
                    'stock' => 10,
                    'date' => $datetime,
                    'type' => 'stock',
                    'experienceGoldenId' => $experienceGoldenId,
                ],
                '2020-10-02' => [
                    'stock' => 0,
                    'date' => (clone $datetime)->modify('+1 day'),
                    'type' => 'stock',
                    'experienceGoldenId' => '1111',
                ],
                '2020-10-03' => [
                    'stock' => 10,
                    'date' => (clone $datetime)->modify('+2 days'),
                    'type' => 'on_request',
                    'experienceGoldenId' => '1111',
                ],
            ],
            (function ($experienceGoldenId, $datetime) {
                $bookingDates = [
                    [
                        'experienceGoldenId' => $experienceGoldenId,
                        'date' => $datetime,
                        'usedStock' => '4',
                    ],
                    [
                        'experienceGoldenId' => $experienceGoldenId,
                        'date' => (clone $datetime)->modify('+1 day'),
                        'usedStock' => '1',
                    ],
                    [
                        'experienceGoldenId' => $experienceGoldenId,
                        'date' => (clone $datetime)->modify('+2 days'),
                        'usedStock' => '2',
                    ],
                ];

                return $bookingDates;
            })($experienceGoldenId, $datetime),
            (function ($test, $usedAvailabilities, $bookingStock, $roomAvailabilities) {
                foreach ($usedAvailabilities as $date => $usedStock) {
                    foreach ($bookingStock as $booking) {
                        if ($booking['date']->format(DateTimeConstants::DEFAULT_DATE_FORMAT) === $date &&
                            $usedStock['experienceGoldenId'] === $booking['experienceGoldenId']) {
                            $realStock = max($roomAvailabilities[$date]['stock'] - $booking['usedStock'], 0);
                            $test->assertEquals(
                                $usedStock['stock'],
                                $realStock
                            );
                        }
                    }
                }
            }),
        ];

        yield 'availability-without-date-stock' => [
            [
                $datetime->format(DateTimeConstants::DEFAULT_DATE_FORMAT) => [
                    'date' => $datetime,
                    'type' => 'stock',
                    'componentGoldenId' => $componentGoldenId,
                ],
                '2020-10-02' => [
                    'stock' => 0,
                    'type' => 'stock',
                    'componentGoldenId' => '1111',
                ],
                '2020-10-03' => [
                    'type' => 'on_request',
                    'componentGoldenId' => '1111',
                ],
            ], $bookingStockDate,
            (function ($test, $usedAvailabilities, $bookingStock, $roomAvailabilities) {
                $test->assertEquals($usedAvailabilities, $roomAvailabilities);
            }),
        ];
    }
}
