<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\AvailabilityHelper;
use PHPUnit\Framework\TestCase;

class AvailabilityHelperTest extends TestCase
{
    private $availabilitiesTestArray = [
        '0', '1', 'r', '0', '1', '1', 'r', 'r', '0', '1',
    ];

    public function testConvertToRequestType()
    {
        $expectedArray = [
            '0', 'r', 'r', '0', 'r', 'r', 'r', 'r', '0', 'r',
        ];

        $this->assertEquals($expectedArray, AvailabilityHelper::convertToRequestType($this->availabilitiesTestArray));
    }

    public function testconvertAvailableValueToRequest()
    {
        $inputString = 'Available';
        $expectedString = 'Request';
        $randomString = 'SomeThingElse';

        $this->assertEquals($expectedString, AvailabilityHelper::convertAvailableValueToRequest($inputString));
        $this->assertEquals($randomString, AvailabilityHelper::convertAvailableValueToRequest($randomString));
    }

    public function testMapRoomAvailabilitiesToExperience()
    {
        $comps = [
            '1234' => [
                [
                    'duration' => 0,
                    'goldenId' => '1234',
                ],
                'experienceGoldenId' => '1111',
            ],
            '4321' => [
                [
                    'duration' => 0,
                    'goldenId' => '4321',
                ],
                'experienceGoldenId' => '2222',
            ],
            '4323' => [
                [
                    'duration' => 0,
                    'goldenId' => '4323',
                ],
                'experienceGoldenId' => '3333',
            ],
            '4324' => [
                [
                    'duration' => 0,
                    'goldenId' => '4324',
                ],
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

    public function testCalculateAvailabilitiesByDuration()
    {
        $roomAvailabilities = [
            0 => [
                'stock' => 10,
                'date' => '2020-07-20',
                'type' => 'instant',
            ],
            1 => [
                'stock' => 10,
                'date' => '2020-07-21',
                'type' => 'instant',
            ],
            2 => [
                'stock' => 0,
                'date' => '2020-07-22',
                'type' => 'instant',
            ],
            3 => [
                'stock' => 10,
                'date' => '2020-07-23',
                'type' => 'instant',
            ],
            4 => [
                'stock' => 10,
                'date' => '2020-07-24',
                'type' => 'instant',
            ],
            5 => [
                'stock' => 10,
                'date' => '2020-07-25',
                'type' => 'instant',
            ],
            6 => [
                'stock' => 0,
                'date' => '2020-07-26',
                'type' => 'instant',
            ],
        ];

        $returnArray = ['1', '1', 'r', '1', '1', '1', 'r'];

        $this->assertEquals($returnArray, AvailabilityHelper::calculateAvailabilitiesByDuration(1, $roomAvailabilities));
    }

    public function testCalculateAvailabilitiesByDurationWithAvailabilityAtLastDate()
    {
        $roomAvailabilities = [
            0 => [
                'stock' => 10,
                'date' => '2020-07-20',
                'type' => 'instant',
            ],
            1 => [
                'stock' => 10,
                'date' => '2020-07-21',
                'type' => 'instant',
            ],
            2 => [
                'stock' => 0,
                'date' => '2020-07-22',
                'type' => 'instant',
            ],
            3 => [
                'stock' => 10,
                'date' => '2020-07-23',
                'type' => 'instant',
            ],
            4 => [
                'stock' => 10,
                'date' => '2020-07-24',
                'type' => 'instant',
            ],
            5 => [
                'stock' => 10,
                'date' => '2020-07-25',
                'type' => 'instant',
            ],
            6 => [
                'stock' => 10,
                'date' => '2020-07-26',
                'type' => 'instant',
            ],
        ];

        $returnArray = ['1', '1', 'r', '1', '1', '1', '1'];

        $this->assertEquals($returnArray, AvailabilityHelper::calculateAvailabilitiesByDuration(1, $roomAvailabilities));
    }

    public function testBuildDataForGetPackage()
    {
        $availabilities = ['1', '1', '1'];
        $duration = 1;
        $numberOfNights = 3;
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
                $numberOfNights,
                $partnerId,
                $isSellable
            )
        );
    }

    public function testBuildDataForGetPackageWithNoAvais()
    {
        $availabilities = [];
        $duration = 1;
        $numberOfNights = 3;
        $partnerId = '1234';
        $isSellable = true;

        $returnArray = [
            'Availabilities' => ['r', 'r', 'r'],
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
                $numberOfNights,
                $partnerId,
                $isSellable
            )
        );
    }
}
