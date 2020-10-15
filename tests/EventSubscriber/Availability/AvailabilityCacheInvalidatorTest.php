<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Availability;

use App\Cache\QuickDataCache;
use App\Entity\Component;
use App\Event\Product\AvailabilityUpdatedEvent;
use App\EventSubscriber\Availability\AvailabilityCacheInvalidator;
use App\Helper\DateDurationExpander;
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
    private $quickDataCache;

    /**
     * @var FlatManageableComponentRepository|ObjectProphecy
     */
    private $flatManageableComponentRepository;

    /**
     * @var DateDurationExpander|ObjectProphecy
     */
    private $dateDurationExpander;

    private AvailabilityCacheInvalidator $availabilityCacheInvalidator;

    public function setUp(): void
    {
        $this->quickDataCache = $this->prophesize(QuickDataCache::class);
        $this->flatManageableComponentRepository = $this->prophesize(FlatManageableComponentRepository::class);
        $this->dateDurationExpander = $this->prophesize(DateDurationExpander::class);

        $this->availabilityCacheInvalidator = new AvailabilityCacheInvalidator(
            $this->quickDataCache->reveal(),
            $this->flatManageableComponentRepository->reveal(),
            $this->dateDurationExpander->reveal()
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
     * @covers ::invalidateCache
     * @covers ::__construct
     * @covers \App\Event\Product\AvailabilityUpdatedEvent::__construct
     */
    public function testInvalidateCache(): void
    {
        $component = new Component();
        $component->goldenId = '1234';

        $dates = ['2020-04-01' => new \DateTime('2020-04-01'), '2020-04-02' => new \DateTime('2020-04-02')];
        $event = new AvailabilityUpdatedEvent($component, $dates);

        $boxes = ['1111', '2222'];

        $this
            ->flatManageableComponentRepository
            ->getBoxesByComponentId('1234')
            ->willReturn($boxes)
            ->shouldBeCalled();

        $expectedDates = [
            '2020-04-01' => new \DateTime('2020-04-01'),
            '2020-04-02' => new \DateTime('2020-04-02'),
        ];

        $this
            ->dateDurationExpander
            ->expandDatesForComponentDuration($component, $dates)
            ->willReturn($expectedDates);

        $keys = [];
        foreach ($expectedDates as $date) {
            foreach ($boxes as $box) {
                $key = $box.'key'.$date->format('Y-m-d');
                $keys[] = $key;
                $this
                    ->quickDataCache
                    ->boxDateKey($box, $date->format('Y-m-d'))
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
