<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class HealthCheckApiTest extends ApiTestCase
{
    public function testPing()
    {
        $response = self::$healthCheckHelper->ping();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }
}
