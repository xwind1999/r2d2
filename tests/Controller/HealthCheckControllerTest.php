<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\HealthCheckController;
use App\Tests\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \App\Controller\HealthCheckController
 */
class HealthCheckControllerTest extends ProphecyTestCase
{
    /**
     * @covers ::ping
     */
    public function testPing()
    {
        $controller = new HealthCheckController();
        $response = $controller->ping();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEquals(null, $response->getContent());
    }
}
