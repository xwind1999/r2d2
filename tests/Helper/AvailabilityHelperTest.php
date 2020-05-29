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
}
