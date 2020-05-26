<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener\Product;

use App\Contract\Request\BroadcastListener\Product\Country;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\Product\Country
 * @group country-request
 */
class CountryTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreateWithString()
    {
        $country = Country::create('FR');

        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('FR', $country->code);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithInteger()
    {
        $this->expectException(\TypeError::class);
        Country::create(1234);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithObject()
    {
        $this->expectException(\TypeError::class);

        $countryCode = new \stdClass();
        $countryCode->code = '4321';
        Country::create($countryCode);
    }
}
