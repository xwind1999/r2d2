<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Constants\DateTimeConstants;
use App\Exception\Helper\InvalidDatesForPeriod;
use App\Helper\DateTimeHelper;
use App\Tests\ProphecyTestCase;

class DateTimeHelperTest extends ProphecyTestCase
{
    /**
     * @dataProvider periodProvider
     * @covers ::createDatePeriod
     */
    public function testCreateDatePeriod($beginDate, $endDate, callable $asserts, string $exception = null)
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $datePeriod = DateTimeHelper::createDatePeriod($beginDate, $endDate);

        $asserts($this, $datePeriod);
    }

    public function periodProvider()
    {
        yield 'three-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('+3 days'),
            (function ($test, $period) {
                $test->assertEquals(
                    (new \DateTime('today'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->start->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
                $test->assertEquals(
                    (new \DateTime('+3 days'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->end->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
            }),
        ];
        yield 'two-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('+2 days'),
            (function ($test, $period) {
                $test->assertEquals(
                    (new \DateTime('today'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->start->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
                $test->assertEquals(
                    (new \DateTime('+2 days'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->end->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
            }),
        ];
        yield 'one-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('+1 day'),
            (function ($test, $period) {
                $test->assertEquals(
                    (new \DateTime('today'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->start->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
                $test->assertEquals(
                    (new \DateTime('+1 day'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->end->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
            }),
        ];
        yield 'same-days-difference-dates' => [
            new \DateTime('today'),
            new \DateTime('today'),
            (function ($test, $period) {
                $test->assertEquals(
                    (new \DateTime('today'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->start->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
                $test->assertEquals(
                    (new \DateTime('+1 day'))->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                    $period->end->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
                );
            }),
            InvalidDatesForPeriod::class,
        ];
    }
}
