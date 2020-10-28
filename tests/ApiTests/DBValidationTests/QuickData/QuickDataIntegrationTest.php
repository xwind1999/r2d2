<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\QuickData;

use App\Constants\AvailabilityConstants;
use App\Constants\DateTimeConstants;
use App\Entity\RoomAvailability;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ApiTests\IntegrationTestCase;

/**
 * Class QuickDataIntegrationTest.
 *
 * @group quickdata
 */
class QuickDataIntegrationTest extends IntegrationTestCase
{
    public function testGetPackage()
    {
        static::cleanUp();

        $experienceId = '7307';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $roomAvailabilities = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsAndPricesByExperienceIdAndDates($experienceId, $dateFrom, $dateTo);
        $resultArray = [];

        foreach ($roomAvailabilities as $availability) {
            if ('stock' === $availability['roomStockType'] && $availability['stock'] > 0) {
                $resultArray[] = '1';
            } elseif ('on_request' === $availability['roomStockType']) {
                $resultArray[] = 'r';
            } else {
                $resultArray[] = '0';
            }
        }
        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => $resultArray,
                'PrestId' => 1,
                'Duration' => $roomAvailabilities[0]['duration'],
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

    public function testGetPackageWithInvalidExperienceId()
    {
        static::cleanUp();

        $experienceId = '7306';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $expectedResult = [
            'ResponseStatus' => [
                'ErrorCode' => 'ArgumentException',
                'Message' => 'Invalid Request',
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
                    'ExtraNight' => boolval($availability['is_sellable']),
                    'ExtraRoom' => boolval($availability['is_sellable']),
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

    /**
     * @throws \Exception
     */
    public function testAvailabilityPricePeriod(): void
    {
        static::cleanUp();

        $experienceId = '7307';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container
            ->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsAndPricesByExperienceIdAndDates($experienceId, $dateFrom, $dateTo)
        ;

        $resultArray = [];
        foreach ($roomAvailabilities as $date => $availability) {
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

            $resultArray[] = $result;
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

    public function testGetRangeV2()
    {
        static::cleanUp();

        $boxId = '851518';
        $dateFrom = new \DateTime(date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime('first day of next month')));

        $avs = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsByBoxId($boxId, $dateFrom);

        $data = [];
        foreach ($avs as $comp => $avs) {
            $d = [
                'Package' => $avs['experienceGoldenId'],
                'Stock' => 0,
                'Request' => 0,
            ];
            if ('on_request' === $avs['roomStockType']) {
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
}
