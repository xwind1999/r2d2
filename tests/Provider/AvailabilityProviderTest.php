<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\CMHub\CMHub;
use App\Contract\Response\CMHub\CMHubErrorResponse;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use App\Provider\AvailabilityProvider;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Provider\AvailabilityProvider
 */
class AvailabilityProviderTest extends TestCase
{
    /**
     * @var CMHub|ObjectProphecy
     */
    protected $cmHub;

    /**
     * @var ObjectProphecy|SerializerInterface
     */
    protected $serializer;

    /**
     * @var ArrayTransformerInterface|ObjectProphecy
     */
    private $arraySerializer;

    public function setUp(): void
    {
        $this->cmHub = $this->prophesize(CMHub::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->arraySerializer = $this->prophesize(ArrayTransformerInterface::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     * @covers \App\Contract\Response\CMHub\GetAvailability\AvailabilityResponse
     */
    public function testGetAvailability()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-04');

        $response = $this->prophesize(ResponseInterface::class);
        $this->cmHub->getAvailability($productId, $dateFrom, $dateTo)->willReturn($response->reveal());

        $argument = '[{"date":"2020-04-28","quantity":40},{"date":"2020-04-29","quantity":40}]';
        $response->getContent()->shouldBeCalled()->willReturn($argument);
        $this->serializer->deserialize(
            $argument,
            sprintf('array<%s>', GetAvailabilityResponse::class), 'json')
            ->willReturn(
                [
                    [
                        'date' => '2020-04-28',
                        'quantity' => 40,
                    ],
            ]
            )->shouldBeCalled();

        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal()
        );
        $response = $availabilityProvider->getAvailability($productId, $dateFrom, $dateTo);

        $this->assertInstanceOf(CMHubResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     * @covers \App\Contract\Response\CMHub\CMHubErrorResponse
     */
    public function testGetAvailabilityThrowsHttpException()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-04');

        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal()
        );

        $result = $this->prophesize(CMHubErrorResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);

        $this->cmHub->getAvailability($productId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $exception->getResponse()->willReturn($responseInterface->reveal());
        $responseInterface->toArray(false)->willReturn(['error' => ['code' => 404, 'message' => 'Not Found']]);

        $this->arraySerializer
            ->fromArray(['code' => 404, 'message' => 'Not Found'], CMHubErrorResponse::class)
            ->willReturn($result->reveal())
        ;

        $this->assertInstanceOf(
            CMHubErrorResponse::class,
            $availabilityProvider->getAvailability($productId, $dateFrom, $dateTo))
        ;
    }
}
