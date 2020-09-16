<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\QuickData;

use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\RoomAvailability;
use App\Repository\ExperienceRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Repository\RoomPriceRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class QuickDataIntegrationTest extends IntegrationTestCase
{
    public function testGetPackage()
    {
        static::cleanUp();

        $experienceId = '7307';
        $dateFrom = new \DateTime(date('Y-m-d', strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $roomAvailabilities = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsByExperienceId($experienceId, $dateFrom, $dateTo);
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

        $response = self::$quickDataHelper->getPackage($experienceId, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'));
        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetPackageV2()
    {
        static::cleanUp();

        $this->consume('event-calculate-flat-manageable-component', 100);

        $experienceIds = ['2611', '7307'];
        $dateFrom = new \DateTime(date('Y-m-d', strtotime('first day of next month')));

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
                $dateFrom->format('Y-m-d'),
                $dateFrom->format('Y-m-d')
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

    public function testAvailabilityPricePeriod()
    {
        static::cleanUp();

        $experienceId = '7307';
        $dateFrom = new \DateTime(date('Y-m-d', strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        /** @var Experience $experience */
        $experience = self::$container->get(ExperienceRepository::class)->findOneByGoldenId($experienceId);

        /** @var ExperienceComponent $experienceComponent */
        $experienceComponent = $experience->experienceComponent->filter(
            static function ($experienceComponent) {
                return $experienceComponent->component->isReservable && $experienceComponent->isEnabled;
            }
        )->first();

        /** @var RoomAvailability[] $roomAvailabilities */
        $roomAvailabilities = self::$container->get(RoomAvailabilityRepository::class)->findByComponentAndDateRange($experienceComponent->component, $dateFrom, $dateTo);
        $resultArray = [];

        foreach ($roomAvailabilities as $date => $availability) {
            try {
                $roomPrice = self::$container->get(RoomPriceRepository::class)->findByComponentAndDateRange($experienceComponent->component, $dateFrom, $dateTo)[$date];
            } catch (\Throwable $exc) {
                $roomPrice = null;
            }
            $result = [
                'Date' => $availability->date->format('Y-m-d\TH:i:s.u'),
                'AvailabilityValue' => $availability->stock,
                'SellingPrice' => null !== $roomPrice ? $roomPrice->price / 100 : 0,
                'BuyingPrice' => null !== $roomPrice ? $roomPrice->price / 100 : 0,
            ];

            if ('stock' === $availability->type && $availability->stock > 0 && false === $availability->isStopSale) {
                $result += [
                    'AvailabilityStatus' => 'Available',
                ];
            } elseif ('on_request' === $availability->type && false === $availability->isStopSale) {
                $result += [
                    'AvailabilityStatus' => 'Request',
                ];
            } else {
                $result += [
                    'AvailabilityStatus' => 'Unavailable',
                    'AvailabilityValue' => 0,
                ];
            }
            $resultArray[] = $result;
        }
        $expectedResult = [
            'DaysAvailabilityPrice' => $resultArray,
        ];

        $response = self::$quickDataHelper->availabilityPricePeriod($experienceId, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'));

        $this->assertEquals($expectedResult, json_decode($response->getContent(), true));
    }

    public function testGetRangeV2()
    {
        static::cleanUp();

        $boxId = '851518';
        $dateFrom = new \DateTime(date('Y-m-d', strtotime('first day of next month')));

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

        $response = json_decode(self::$quickDataHelper->getRangeV2($boxId, $dateFrom->format('Y-m-d'))->getContent(), true);

        usort($response['PackagesList'], function ($current, $next) {
            return $current['Package'] > $next['Package'];
        });

        $this->assertEquals($expectedResult, $response);
    }
}
