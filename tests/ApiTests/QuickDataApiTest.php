<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class QuickDataApiTest extends ApiTestCase
{
    public function testGetPackage(): void
    {
        $response = self::$quickDataHelper->getPackage(
            18474,
            '2020-09-01',
            '2020-09-05'
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetPackageBadRequest(): void
    {
        $response = self::$quickDataHelper->getPackage(
            124125234332,
            '2020-09-01',
            '2020-09-05'
        );
        $this->assertEquals(400, $response->getStatusCode());
    }
}
