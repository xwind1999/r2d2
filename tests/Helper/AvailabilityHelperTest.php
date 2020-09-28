<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Exception\Helper\InvalidDatesForPeriod;
use App\Helper\AvailabilityHelper;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Helper\AvailabilityHelper
 */
class AvailabilityHelperTest extends TestCase
{
    /**
     * @covers ::convertAvailabilityTypeToExplicitQuickdataValue
     */
    public function testConvertAvailableValueToRequest(): void
    {
        $this->assertEquals(
            'Unavailable',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                0,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                0,
                '1'
            )
        );
        $this->assertEquals(
            'Available',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                1,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'stock',
                1,
                '1'
            )
        );
        $this->assertEquals(
            'Request',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                0,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                0,
                '1'
            )
        );
        $this->assertEquals(
            'Request',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                'on_request',
                1,
                '0'
            )
        );
        $this->assertEquals(
            'Unavailable',
            AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
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
        $this->assertEquals($expectedString, AvailabilityHelper::getRoomStockShortType($inputString));
    }

    /**
     * @covers ::buildDataForGetPackage
     */
    public function testBuildDataForGetPackageV2(): void
    {
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
            AvailabilityHelper::buildDataForGetPackage(
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
        $dateFrom = new \DateTime('2020-10-01');
        $dateTo = new \DateTime('2020-10-02');
        $roomStockType = '1';
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
        ];

        $returnArray = [
            'Availabilities' => ['1', '1'],
            'PrestId' => 1,
            'Duration' => 1,
            'LiheId' => 1,
            'PartnerCode' => '1234',
            'ExtraNight' => false,
            'ExtraRoom' => false,
        ];

        $this->assertEquals(
            $returnArray,
            AvailabilityHelper::fillMissingAvailabilityForGetPackage(
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
        $dateFrom = new \DateTime('2020-10-01');
        $dateTo = new \DateTime('2020-10-02');
        $roomStockType = '1';
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
            AvailabilityHelper::fillMissingAvailabilityForGetPackage(
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
            AvailabilityHelper::buildDataForGetRange($availabilities)
        );
    }

    /**
     * @covers ::convertToShortType
     */
    public function testConvertToShortTypeInstant(): void
    {
        $stockList = ['1', '2', '3', '0', '1', '0', '5', '1'];
        $returnArray = ['1', '1', '1', '0', '1', '0', '1', '1'];

        $this->assertEquals(
            $returnArray,
            AvailabilityHelper::convertToShortType(
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
        $stockList = ['1', '2', '3', '0', '1', '0', '5', '1'];
        $returnArray = ['r', 'r', 'r', '0', 'r', '0', 'r', 'r'];

        $this->assertEquals(
            $returnArray,
            AvailabilityHelper::convertToShortType(
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
            AvailabilityHelper::fillMissingAvailabilitiesForAvailabilityPrice($availabilities, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::fillMissingAvailabilitiesForAvailabilityPrice
     */
    public function testFillMissingAvailabilitiesWithEnoughDate(): void
    {
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
            AvailabilityHelper::fillMissingAvailabilitiesForAvailabilityPrice($availabilities, $dateFrom, $dateTo)
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
     * @covers ::getRealStockByDate
     */
    public function testGetRoomAvailabilityRealStock(array $availabilities, array $bookingStockDate, callable $asserts)
    {
        $usedAvailabilities = AvailabilityHelper::getRealStockByDate($availabilities, $bookingStockDate);

        $asserts($this, $usedAvailabilities, $bookingStockDate, $availabilities);
    }

    public function bookingAvailabilityProvider()
    {
        $componentGoldenId = '12345';
        $datetime = new \DateTime('2020-10-01');

        $availabilities = [
            $datetime->format('Y-m-d') => [
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
                        if ($booking['date']->format('Y-m-d') === $date) {
                            $realStock = $booking['usedStock'] > $roomAvailabilities[$date]['stock'] ? 0 :
                                $roomAvailabilities[$date]['stock'] - $booking['usedStock'];
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
                        if ($booking['date']->format('Y-m-d') === $date) {
                            $realStock = $booking['usedStock'] > $roomAvailabilities[$date]['stock'] ? 0 :
                                $roomAvailabilities[$date]['stock'] - $booking['usedStock'];
                            $test->assertEquals(
                                $usedStock['stock'],
                                $realStock
                            );
                        }
                    }
                }
            }),
        ];
    }

    /**
     * @dataProvider periodProvider
     * @covers ::createDatePeriod
     */
    public function testCreateDatePeriod($beginDate, $endDate, callable $asserts, string $exception = null)
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $datePeriod = AvailabilityHelper::createDatePeriod($beginDate, $endDate);

        $asserts($this, $datePeriod);
    }

    public function periodProvider()
    {
        yield 'three-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('+3 days'),
            (function ($test, $period) {
                $test->assertEquals((new \DateTime('today'))->format('Y-m-d'), $period->start->format('Y-m-d'));
                $test->assertEquals((new \DateTime('+3 days'))->format('Y-m-d'), $period->end->format('Y-m-d'));
            }),
        ];
        yield 'two-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('+2 days'),
            (function ($test, $period) {
                $test->assertEquals((new \DateTime('today'))->format('Y-m-d'), $period->start->format('Y-m-d'));
                $test->assertEquals((new \DateTime('+2 days'))->format('Y-m-d'), $period->end->format('Y-m-d'));
            }),
        ];
        yield 'one-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('+1 day'),
            (function ($test, $period) {
                $test->assertEquals((new \DateTime('today'))->format('Y-m-d'), $period->start->format('Y-m-d'));
                $test->assertEquals((new \DateTime('+1 day'))->format('Y-m-d'), $period->end->format('Y-m-d'));
            }),
        ];
        yield 'same-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('today'),
            (function ($test, $period) {
                $test->assertEquals((new \DateTime('today'))->format('Y-m-d'), $period->start->format('Y-m-d'));
                $test->assertEquals((new \DateTime('+1 day'))->format('Y-m-d'), $period->end->format('Y-m-d'));
            }),
            InvalidDatesForPeriod::class,
        ];
    }
}
