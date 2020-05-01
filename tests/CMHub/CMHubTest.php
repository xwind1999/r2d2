<?php

declare(strict_types=1);

namespace App\Tests\CMHub;

use App\CMHub\CMHub;
use App\Http\HttpClient;
use App\Http\HttpClientFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\CMHub\CMHub
 */
class CMHubTest extends TestCase
{
    /**
     * @var HttpClientFactory|ObjectProphecy
     */
    protected $httpClientFactory;

    /**
     * @var HttpClient|ObjectProphecy
     */
    protected $httpClient;

    public function setUp(): void
    {
        $this->httpClientFactory = $this->prophesize(HttpClientFactory::class);
        $this->httpClient = $this->prophesize(HttpClient::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     */
    public function testGetAvailability()
    {
        $this->httpClientFactory->buildWithOptions(Argument::type('string'), [])->willReturn($this->httpClient->reveal());
        $cmHub = new CMHub([], $this->httpClientFactory->reveal());

        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-05');

        $response = $this->prophesize(ResponseInterface::class);
        $response->toArray()->willReturn([]);

        $this->httpClient->request(Argument::any(), Argument::any(), [
            'start' => $dateFrom->format('Y-m-d'),
            'end' => $dateTo->format('Y-m-d'),
        ])->willReturn($response->reveal())->shouldBeCalled();

        $this->assertInstanceOf(ResponseInterface::class, $cmHub->getAvailability($productId, $dateFrom, $dateTo));
    }
}
