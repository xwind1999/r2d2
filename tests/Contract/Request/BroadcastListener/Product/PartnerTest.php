<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener\Product;

use App\Contract\Request\BroadcastListener\Product\Partner;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\Product\Partner
 * @group partner-request
 */
class PartnerTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreateWithString()
    {
        $partner = Partner::create('1324');

        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertEquals('1324', $partner->id);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithInteger()
    {
        $this->expectException(\TypeError::class);
        Partner::create(1234);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithObject()
    {
        $this->expectException(\TypeError::class);

        $obj = new \stdClass();
        $obj->id = '4321';
        Partner::create($obj);
    }
}
