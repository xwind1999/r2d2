<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class QuickDataApiTest extends ApiTestCase
{
    public function testGetPackage(): void
    {
        $response = self::$quickDataHelper->getPackage(
            7307,
            date('Y-m-d', strtotime('first day of next month')),
            date('Y-m-\0\5', strtotime('first day of next month'))
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetPackageBadRequest(): void
    {
        $response = self::$quickDataHelper->getPackage(
            124125234332,
            date('Y-m-d', strtotime('first day of next month')),
            date('Y-m-\0\5', strtotime('first day of next month'))
        );
        $this->assertEquals(400, $response->getStatusCode());
    }
}
