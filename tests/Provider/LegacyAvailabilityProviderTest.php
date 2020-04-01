<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Provider\LegacyAvailabilityProvider;
use App\QuickData\QuickData;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class LegacyAvailabilityProviderTest extends TestCase
{
    /**
     * @var ObjectProphecy|QuickData
     */
    protected $quickData;

    /**
     * @var ArrayTransformerInterface|ObjectProphecy
     */
    protected $serializer;

    public function setUp(): void
    {
        $this->quickData = $this->prophesize(QuickData::class);
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);
    }

    public function testGetAvailabilityForExperience()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(GetPackageResponse::class);
        $this->quickData->getPackage($experienceId, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    public function testGetAvailabilityForExperienceWillFailDueToHttpError()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(QuickDataErrorResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);
        $this->quickData->getPackage($experienceId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $exception->getResponse()->willReturn($responseInterface->reveal());
        $responseInterface->getStatusCode()->willReturn(405);
        $responseInterface->toArray(false)->willReturn([]);

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(QuickDataErrorResponse::class, $response);
    }
}
