<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\Product;

use App\Contract\Request\BroadcastListener\Product\Brand;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\Product\Brand
 * @group brand-request
 */
class BrandTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreateWithString()
    {
        $brand = Brand::create('SBX');

        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertEquals('SBX', $brand->code);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithInteger()
    {
        $this->expectException(\TypeError::class);
        Brand::create(1234);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithObject()
    {
        $this->expectException(\TypeError::class);

        $brandCode = new \stdClass();
        $brandCode->code = '4321';
        Brand::create($brandCode);
    }
}
