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
    public function testconvertAvailableValueToRequest()
    {
        $this->assertEquals('Unavailable', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('stock', 0, false));
        $this->assertEquals('Unavailable', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('stock', 0, true));
        $this->assertEquals('Available', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('stock', 1, false));
        $this->assertEquals('Unavailable', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('stock', 1, true));
        $this->assertEquals('Request', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('on_request', 0, false));
        $this->assertEquals('Unavailable', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('on_request', 0, true));
        $this->assertEquals('Request', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('on_request', 1, false));
        $this->assertEquals('Unavailable', AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue('on_request', 1, true));
    }

    public function testMapRoomAvailabilitiesToExperience()
    {
        $comps = [
            '1234' => [
                'duration' => 0,
                'goldenId' => '1234',
                'experienceGoldenId' => '1111',
            ],
            '4321' => [
                'duration' => 0,
                'goldenId' => '4321',
                'experienceGoldenId' => '2222',
            ],
            '4323' => [
                'duration' => 0,
                'goldenId' => '4323',
                'experienceGoldenId' => '3333',
            ],
            '4324' => [
                'duration' => 0,
                'goldenId' => '4324',
                'experienceGoldenId' => '4444',
            ],
        ];

        $roomAvailabilities = [
            '1234' => [
                'stock' => 10,
                'componentGoldenId' => '1234',
            ],
            '4321' => [
                'stock' => 5,
                'componentGoldenId' => '4321',
            ],
            '4322' => [
                'stock' => 6,
                'componentGoldenId' => '4322',
            ],
        ];

        $returnArray = [
            [
                'Package' => '1111',
                'Request' => 0,
                'Stock' => 5,
            ],
            [
                'Package' => '2222',
                'Request' => 0,
                'Stock' => 5,
            ],
            [
                'Package' => '3333',
                'Request' => 5,
                'Stock' => 0,
            ],
            [
                'Package' => '4444',
                'Request' => 5,
                'Stock' => 0,
            ],
        ];

        $this->assertEquals(AvailabilityHelper::mapRoomAvailabilitiesToExperience($comps, $roomAvailabilities, 5), $returnArray);
    }

    public function testBuildDataForGetPackage()
    {
        $availabilities = ['1', '1', '1'];
        $duration = 1;
        $partnerId = '1234';
        $isSellable = true;

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
                $isSellable
            )
        );
    }

    public function testBuildDataForGetRange()
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

    public function testConvertToShortType()
    {
        $availabilities = [
            0 => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-20'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
            ],
            1 => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-21'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
            ],
            2 => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-22'),
                'type' => 'on_request',
                'componentGoldenId' => '1111',
            ],
        ];

        $returnArray = ['1', '0', 'r'];

        $this->assertEquals($returnArray, AvailabilityHelper::convertToShortType($availabilities));
    }

    public function testFillMissingAvailabilities()
    {
        $availabilities = [
            '2020-06-20' => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-20'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
            '2020-06-21' => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-21'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
            '2020-06-23' => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-23'),
                'type' => 'on_request',
                'componentGoldenId' => '1111',
                'isStopSale' => false,
            ],
        ];

        $componentId = '1111';
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $returnArray = [
            '2020-06-20' => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-20'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
            '2020-06-21' => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-21'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
            '2020-06-22' => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-22'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
            '2020-06-23' => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-23'),
                'type' => 'on_request',
                'componentGoldenId' => '1111',
                'isStopSale' => false,
            ],
            '2020-06-24' => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-24'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
            '2020-06-25' => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-25'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
                'isStopSale' => true,
            ],
        ];

        $this->assertEquals(
            $returnArray,
            AvailabilityHelper::fillMissingAvailabilities($availabilities, $componentId, $dateFrom, $dateTo)
        );
    }

    public function testFillMissingAvailabilitiesWithEnoughDate()
    {
        $availabilities = [
            '2020-06-20' => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-20'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
            ],
            '2020-06-21' => [
                'stock' => 0,
                'date' => new \DateTime('2020-06-21'),
                'type' => 'stock',
                'componentGoldenId' => '1111',
            ],
            '2020-06-22' => [
                'stock' => 10,
                'date' => new \DateTime('2020-06-22'),
                'type' => 'on_request',
                'componentGoldenId' => '1111',
            ],
        ];

        $componentId = '1111';
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-22');

        $this->assertEquals(
            $availabilities,
            AvailabilityHelper::fillMissingAvailabilities($availabilities, $componentId, $dateFrom, $dateTo)
        );
    }
}
