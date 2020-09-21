<?php

declare(strict_types=1);

namespace App\Tests\Helper;

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
    public function testBuildDataForGetPackage(): void
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
     * @covers ::fillMissingAvailabilities
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
            AvailabilityHelper::fillMissingAvailabilities($availabilities, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::fillMissingAvailabilities
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
            AvailabilityHelper::fillMissingAvailabilities($availabilities, $dateFrom, $dateTo)
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
     */
    public function testGetRoomAvailabilityRealStock(array $availabilities, array $bookingStockDate, callable $asserts)
    {
        $usedAvailabilities = AvailabilityHelper::getRealStockByDate($availabilities, $bookingStockDate);

        $asserts($this, $usedAvailabilities, $bookingStockDate, $availabilities);
    }

    /**
     * @dataProvider bookingAvailabilityRealStockProvider
     */
    public function testGetRealStock(array $availabilities, array $bookingStockDate, callable $asserts)
    {
        $usedAvailabilities = AvailabilityHelper::getRealStock($availabilities, $bookingStockDate);

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
                            $test->assertEquals(
                                $usedStock['stock'],
                                ($roomAvailabilities[$date]['stock'] - $booking['usedStock'])
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

    public function bookingAvailabilityRealStockProvider()
    {
        $datetime = new \DateTime('2020-10-01');

        $availabilities = [
            [
                'experience_golden_id' => '59593',
                'partner_golden_id' => '00030786',
                'is_sellable' => '0',
                'duration' => 1,
                'date' => $datetime->format('Y-m-d'),
                'stock' => 1,
            ],
            [
                'experience_golden_id' => '59593',
                'partner_golden_id' => '00030786',
                'is_sellable' => '0',
                'duration' => 1,
                'date' => (clone $datetime)->modify('+1 day')->format('Y-m-d'),
                'stock' => 10,
            ],         [
                'experience_golden_id' => '59593',
                'partner_golden_id' => '00030786',
                'is_sellable' => '0',
                'duration' => 1,
                'date' => (clone $datetime)->modify('+2 days')->format('Y-m-d'),
                'stock' => 5,
            ],
            [
                'experience_golden_id' => '59593',
                'partner_golden_id' => '00030786',
                'is_sellable' => '0',
                'duration' => 1,
                'date' => (clone $datetime)->modify('+3 days')->format('Y-m-d'),
                'stock' => 3,
            ],
        ];

        $bookingStockDate = [
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => $datetime,
                'usedStock' => 1,
            ],
            [
                'experienceGoldenId' => '59593',
                'componentGoldenId' => '213072',
                'date' => (clone $datetime)->modify('+3 days'),
                'usedStock' => 5,
            ],
        ];

        yield 'booking-availability-calculate' => [
            $availabilities,
            $bookingStockDate,
            (function ($test, $usedAvailabilities, $bookingStock, $roomAvailabilities) {
                foreach ($usedAvailabilities as $key => $usedAvailability) {
                    foreach ($bookingStock as $booking) {
                        if ($booking['date']->format('Y-m-d') === $usedAvailability['date']) {
                            $realStock = $booking['usedStock'] > $roomAvailabilities[$key]['stock'] ? 0 :
                                $roomAvailabilities[$key]['stock'] - $booking['usedStock'];
                            $test->assertEquals(
                                $usedAvailability['stock'],
                                $realStock
                            );
                        }
                    }
                }
            }),
        ];
    }
}
