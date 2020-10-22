<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Entity\Component;
use App\Helper\DateDurationExpander;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Helper\DateDurationExpander
 */
class DateDurationExpanderTest extends ProphecyTestCase
{
    private DateDurationExpander $dateDurationExpander;

    public function setUp(): void
    {
        $this->dateDurationExpander = new DateDurationExpander();
    }

    /**
     * @covers ::expandDatesForComponentDuration
     */
    public function testExpandDatesForComponentDuration(): void
    {
        $component = new Component();
        $component->duration = 1;
        $component->goldenId = '1234';

        $dates = ['2020-04-01' => new \DateTime('2020-04-01'), '2020-04-02' => new \DateTime('2020-04-02')];

        $expectedDates = [
            '2020-04-01' => new \DateTime('2020-04-01'),
            '2020-04-02' => new \DateTime('2020-04-02'),
        ];

        $this->assertEquals($expectedDates, $this->dateDurationExpander->expandDatesForComponentDuration($component, $dates));
    }

    /**
     * @covers ::expandDatesForComponentDuration
     */
    public function testExpandDatesForComponentDurationEquals3(): void
    {
        $component = new Component();
        $component->duration = 3;
        $component->goldenId = '1234';

        $dates = [
            '2020-04-05' => new \DateTime('2020-04-05'),
            '2020-04-10' => new \DateTime('2020-04-10'),
        ];

        $expectedDates = [
            '2020-04-03' => new \DateTime('2020-04-03'),
            '2020-04-04' => new \DateTime('2020-04-04'),
            '2020-04-05' => new \DateTime('2020-04-05'),
            '2020-04-06' => new \DateTime('2020-04-06'),
            '2020-04-07' => new \DateTime('2020-04-07'),
            '2020-04-08' => new \DateTime('2020-04-08'),
            '2020-04-09' => new \DateTime('2020-04-09'),
            '2020-04-10' => new \DateTime('2020-04-10'),
            '2020-04-11' => new \DateTime('2020-04-11'),
            '2020-04-12' => new \DateTime('2020-04-12'),
        ];

        $this->assertEquals($expectedDates, $this->dateDurationExpander->expandDatesForComponentDuration($component, $dates));
    }
}
