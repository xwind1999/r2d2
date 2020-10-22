<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener\Product;

use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\Product\Universe
 * @group universe-request
 */
class UniverseTest extends ProphecyTestCase
{
    /**
     * @covers ::create
     */
    public function testCreateWithString()
    {
        $universe = Universe::create('universe');

        $this->assertInstanceOf(Universe::class, $universe);
        $this->assertEquals('universe', $universe->id);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithInteger()
    {
        $this->expectException(\TypeError::class);
        Universe::create(1234);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithObject()
    {
        $this->expectException(\TypeError::class);

        $universe = new \stdClass();
        $universe->id = '4321';
        Universe::create($universe);
    }
}
