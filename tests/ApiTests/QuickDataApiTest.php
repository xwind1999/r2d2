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

    public function testGetPackageV2(): void
    {
        $response = self::$quickDataHelper->getPackageV2(
            '43287,52208',
            '2020-09-01',
            '2020-09-05'
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetPackageV2BadRequest(): void
    {
        $response = self::$quickDataHelper->getPackageV2(
            '124125234332',
            '2020-09-01',
            '2020-09-05'
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
