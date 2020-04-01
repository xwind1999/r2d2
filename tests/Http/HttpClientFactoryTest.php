<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\HttpClient;
use App\Http\HttpClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HttpClientFactoryTest extends TestCase
{
    public function testBuildWithOptions()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $factory = new HttpClientFactory($dispatcher->reveal());

        $httpClient = $factory->buildWithOptions('client', []);

        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }
}
