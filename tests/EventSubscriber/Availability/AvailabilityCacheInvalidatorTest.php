<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Availability;

use App\Cache\QuickDataCache;
use App\Entity\Component;
use App\Event\Product\AvailabilityUpdatedEvent;
use App\EventSubscriber\Availability\AvailabilityCacheInvalidator;
use App\Repository\Flat\FlatManageableComponentRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\EventSubscriber\Availability\AvailabilityCacheInvalidator
 */
class AvailabilityCacheInvalidatorTest extends TestCase
{
    /**
     * @var ObjectProphecy|QuickDataCache
     */
    private ObjectProphecy $quickDataCache;

    /**
     * @var FlatManageableComponentRepository|ObjectProphecy
     */
    private $flatManageableComponentRepository;

    private AvailabilityCacheInvalidator $availabilityCacheInvalidator;

    public function setUp(): void
    {
        $this->quickDataCache = $this->prophesize(QuickDataCache::class);
        $this->flatManageableComponentRepository = $this->prophesize(FlatManageableComponentRepository::class);

        $this->availabilityCacheInvalidator = new AvailabilityCacheInvalidator(
            $this->quickDataCache->reveal(),
            $this->flatManageableComponentRepository->reveal()
        );
    }

    /**
     * @covers ::getSubscribedEvents
     * @covers ::__construct
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [AvailabilityUpdatedEvent::class => ['invalidateCache']],
            AvailabilityCacheInvalidator::getSubscribedEvents()
        );
    }

    /**
     * @covers ::expandCacheDates
     * @covers ::invalidateCache
     * @covers ::__construct
     * @covers \App\Event\Product\AvailabilityUpdatedEvent::__construct
     */
    public function testInvalidateCache(): void
    {
        $component = new Component();
        $component->duration = 1;
        $component->goldenId = '1234';

        $dates = ['2020-04-01' => 1, '2020-04-02' => 1];
        $event = new AvailabilityUpdatedEvent($component, $dates);

        $boxes = ['1111', '2222'];

        $this
            ->flatManageableComponentRepository
            ->getBoxesByComponentId('1234')
            ->willReturn($boxes)
            ->shouldBeCalled();

        $expectedDates = ['2020-04-01', '2020-04-02'];

        $keys = [];
        foreach ($expectedDates as $date) {
            foreach ($boxes as $box) {
                $key = $box.'key'.$date;
                $keys[] = $key;
                $this
                    ->quickDataCache
                    ->boxDateKey($box, $date)
                    ->willReturn($key)
                    ->shouldBeCalledTimes(1);
            }
        }

        $this->quickDataCache->massInvalidate(Argument::that(function ($v) use ($keys) {
            sort($keys);
            sort($v);

            return $keys === $v;
        }))->shouldBeCalled();

        $this->availabilityCacheInvalidator->invalidateCache($event);
    }

    /**
     * @covers ::expandCacheDates
     * @covers ::invalidateCache
     * @covers ::__construct
     * @covers \App\Event\Product\AvailabilityUpdatedEvent::__construct
     */
    public function testInvalidateCacheWhenComponentDurationEquals3(): void
    {
        $component = new Component();
        $component->duration = 3;
        $component->goldenId = '1234';

        $dates = [
            '2020-04-05' => 1,
            '2020-04-10' => 1,
        ];
        $event = new AvailabilityUpdatedEvent($component, $dates);

        $boxes = ['1111', '2222'];

        $this
            ->flatManageableComponentRepository
            ->getBoxesByComponentId('1234')
            ->willReturn($boxes)
            ->shouldBeCalled();

        $expectedDates = [
            '2020-04-03',
            '2020-04-04',
            '2020-04-05',
            '2020-04-06',
            '2020-04-07',
            '2020-04-08',
            '2020-04-09',
            '2020-04-10',
            '2020-04-11',
            '2020-04-12',
        ];
        $keys = [];
        foreach ($expectedDates as $date) {
            foreach ($boxes as $box) {
                $key = $box.'key'.$date;
                $keys[] = $key;
                $this
                    ->quickDataCache
                    ->boxDateKey($box, $date)
                    ->willReturn($key)
                    ->shouldBeCalledTimes(1);
            }
        }

        $this->quickDataCache->massInvalidate(Argument::that(function ($v) use ($keys) {
            sort($keys);
            sort($v);

            return $keys === $v;
        }))->shouldBeCalled();

        $this->availabilityCacheInvalidator->invalidateCache($event);
    }
}
