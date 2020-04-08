<?php

declare(strict_types=1);

namespace App\Tests\QuickData;

use App\Http\HttpClient;
use App\Http\HttpClientFactory;
use App\QuickData\QuickData;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\ResponseInterface;

class QuickDataTest extends TestCase
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

    public function testGetPackage()
    {
        $this->httpClientFactory->buildWithOptions(Argument::type('string'), [])->willReturn($this->httpClient->reveal());
        $quickData = new QuickData([], $this->httpClientFactory->reveal());

        $packageCode = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $response = $this->prophesize(ResponseInterface::class);
        $response->toArray()->willReturn([]);

        $this->httpClient->request(Argument::any(), Argument::any(), [
            'format' => 'json',
            'PackageCode' => $packageCode,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ])->willReturn($response->reveal())->shouldBeCalled();
        $this->assertEquals([], $quickData->getPackage($packageCode, $dateFrom, $dateTo));
    }

    public function testGetRange()
    {
        $this->httpClientFactory->buildWithOptions(Argument::type('string'), [])->willReturn($this->httpClient->reveal());
        $quickData = new QuickData([], $this->httpClientFactory->reveal());

        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $response = $this->prophesize(ResponseInterface::class);
        $response->toArray()->willReturn([]);

        $this->httpClient->request(Argument::any(), Argument::any(), [
            'format' => 'json',
            'boxVersion' => $boxId,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ])->willReturn($response->reveal())->shouldBeCalled();
        $this->assertEquals([], $quickData->getRange($boxId, $dateFrom, $dateTo));
    }

    public function testGetPackageV2()
    {
        $this->httpClientFactory->buildWithOptions(Argument::type('string'), [])->willReturn($this->httpClient->reveal());
        $quickData = new QuickData([], $this->httpClientFactory->reveal());

        $listPackageCodeArray = [1234, 5678];
        $listPackageCode = '1234,5678';
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $response = $this->prophesize(ResponseInterface::class);
        $response->toArray()->willReturn([]);

        $this->httpClient->request(Argument::any(), Argument::any(), [
            'format' => 'json',
            'listPackageCode' => $listPackageCode,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ])->willReturn($response->reveal())->shouldBeCalled();
        $this->assertEquals([], $quickData->getPackageV2($listPackageCodeArray, $dateFrom, $dateTo));
    }
}
