<?php

declare(strict_types=1);

namespace App\Tests\Cache;

use App\Cache\MemcachedWrapper;
use App\Cache\QuickDataCache;
use App\Contract\Response\QuickData\GetRangeResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Cache\QuickDataCache
 */
class QuickDataCacheTest extends TestCase
{
    /**
     * @var MemcachedWrapper|ObjectProphecy
     */
    private $memcachedWrapper;

    private QuickDataCache $quickDataCache;

    public function setUp(): void
    {
        $this->memcachedWrapper = $this->prophesize(MemcachedWrapper::class);
        $this->quickDataCache = new QuickDataCache($this->memcachedWrapper->reveal());
    }

    /**
     * @covers ::getBoxDate
     * @covers ::boxDateKey
     * @covers ::__construct
     */
    public function testGetBoxDateWillHitCache(): void
    {
        $data = new GetRangeResponse();
        $data->packagesList = ['1234'];

        $boxId = 'box';
        $date = '2020-10-01';

        $this->memcachedWrapper->get('box.'.$boxId.'.'.$date)->shouldBeCalled()->willReturn($data);
        $this->assertEquals($data, $this->quickDataCache->getBoxDate($boxId, $date));
    }

    /**
     * @covers ::getBoxDate
     * @covers ::boxDateKey
     * @covers ::__construct
     */
    public function testGetBoxDateWillNotHitCache(): void
    {
        $boxId = 'box';
        $date = '2020-10-01';

        $this->memcachedWrapper->get('box.'.$boxId.'.'.$date)->shouldBeCalled()->willReturn(false);
        $this->expectException(\Exception::class);
        $this->quickDataCache->getBoxDate($boxId, $date);
    }

    /**
     * @covers ::setBoxDate
     * @covers ::boxDateKey
     * @covers ::__construct
     */
    public function testSetBoxDate(): void
    {
        $data = new GetRangeResponse();
        $data->packagesList = ['1234'];

        $boxId = 'box';
        $date = '2020-10-01';

        $this->memcachedWrapper->set('box.'.$boxId.'.'.$date, $data, 3600)->shouldBeCalled()->willReturn(true);
        $this->quickDataCache->setBoxDate($boxId, $date, $data);
    }

    /**
     * @covers ::massInvalidate
     * @covers ::__construct
     */
    public function testMasInvalidate(): void
    {
        $data = ['1234', '5678'];
        $this->memcachedWrapper->deleteMulti($data)->shouldBeCalled();
        $this->quickDataCache->massInvalidate($data);
    }
}
