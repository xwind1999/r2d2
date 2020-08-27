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

        foreach ($roomAvailabilities as $availability) {
            if ('stock' === $availability->type && $availability->stock > 0 && false === $availability->isStopSale) {
                $resultArray[] = '1';
            } elseif ('on_request' === $availability->type && false === $availability->isStopSale) {
                $resultArray[] = 'r';
            } else {
                $resultArray[] = '0';
            }
        }
        $expectedResult = [
            'ListPrestation' => [[
                'Availabilities' => $resultArray,
                'PrestId' => 1,
                'Duration' => $experienceComponent->component->duration,
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

        $experienceIds = ['2611', '7307'];
        $dateFrom = new \DateTime(date('Y-m-d', strtotime('first day of next month')));
        $dateTo = (clone $dateFrom)->modify('+5 day');

        $expectedResults = [];
        foreach ($experienceIds as $experienceId) {
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

            foreach ($roomAvailabilities as $availability) {
                if ('stock' === $availability->type && $availability->stock > 0 && false === $availability->isStopSale) {
                    $resultArray[] = '1';
                } elseif ('on_request' === $availability->type && false === $availability->isStopSale) {
                    $resultArray[] = 'r';
                } else {
                    $resultArray[] = '0';
                }
            }

            $expectedResults[] = [
                'PackageCode' => (int) $experienceId,
                'ListPrestation' => [[
                    'Availabilities' => $resultArray,
                    'PrestId' => 1,
                    'Duration' => $experienceComponent->component->duration,
                    'LiheId' => 1,
                    'PartnerCode' => $experienceComponent->component->partnerGoldenId,
                    'ExtraNight' => false,
                    'ExtraRoom' => false,
                ]],
            ];
        }

        $expected = [
            'ListPackage' => $expectedResults,
        ];

        $response = json_decode(self::$quickDataHelper->getPackageV2(implode(',', $experienceIds), $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'))->getContent(), true);
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
        $dateTo = (clone $dateFrom)->modify('+5 day');
        $nights = 6;

        $avs = self::$container->get(RoomAvailabilityRepository::class)
            ->findAvailableRoomsByBoxId($boxId, $dateFrom, $dateTo);

        $data = [];
        foreach ($avs as $comp => $avs) {
            $itemCounts = array_count_values(
                explode(',', $avs['roomAvailabilities'])
            );

            $data[] = [
                'Package' => $avs['experienceGoldenId'],
                'Stock' => $itemCounts['stock'] ?? 0,
                'Request' => $itemCounts['on_request'] ?? 0,
            ];
        }

        $expectedResult = [
            'PackagesList' => $data,
        ];

        usort($expectedResult['PackagesList'], function ($current, $next) {
            return $current['Package'] > $next['Package'];
        });

        $response = json_decode(self::$quickDataHelper->getRangeV2($boxId, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'))->getContent(), true);

        usort($response['PackagesList'], function ($current, $next) {
            return $current['Package'] > $next['Package'];
        });

        $this->assertEquals($expectedResult, $response);
    }
}
