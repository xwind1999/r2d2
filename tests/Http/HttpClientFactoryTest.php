<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\CorrelationId\CorrelationId;
use App\Http\HttpClient;
use App\Http\HttpClientFactory;
use App\Tests\ProphecyTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \App\Http\HttpClientFactory
 * @group http
 */
class HttpClientFactoryTest extends ProphecyTestCase
{
    public function testBuildWithOptions()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $correlationId = $this->prophesize(CorrelationId::class);
        $correlationId->getCorrelationId()->willReturn(Uuid::uuid4());
        $factory = new HttpClientFactory($dispatcher->reveal(), $correlationId->reveal());

        $httpClient = $factory->buildWithOptions('client', []);

        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }

    /**
     * @dataProvider requestProvider
     */
    public function testBuildWithCorrelationId(RequestStack $requestStack, callable $asserts)
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $correlationId = new CorrelationId($requestStack);

        $factory = new HttpClientFactory($dispatcher->reveal(), $correlationId);

        $httpClient = $factory->buildWithOptions('client', []);

        $reflectedCurlHttp = $this->getHttpClientReflection($httpClient);
        $reflectedHttpOptions = $this->getHttpClientOptionsReflection($reflectedCurlHttp);

        $asserts($httpClient, $reflectedHttpOptions, $requestStack);
    }

    private function getHttpClientReflection(HttpClient $httpClient, $property = 'httpClient')
    {
        $reflector = new \ReflectionClass($httpClient);
        $reflectorProperty = $reflector->getProperty($property);
        $reflectorProperty->setAccessible(true);

        return $reflectorProperty->getValue($httpClient);
    }

    private function getHttpClientOptionsReflection(CurlHttpClient $curlHttpClient, $property = 'defaultOptions')
    {
        $reflector = new \ReflectionClass($curlHttpClient);
        $reflectorProperty = $reflector->getProperty($property);
        $reflectorProperty->setAccessible(true);

        return $reflectorProperty->getValue($curlHttpClient);
    }

    public function requestProvider(): \Generator
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $headers = [
            'accept' => ['application/json'],
            'content-type' => ['application/json'],
        ];

        yield 'request-with-correlation-id' => [
            (function ($requestStack, $headers, $request) {
                $request->headers->add($headers);
                $request->headers->set(CorrelationId::HEADER_KEY, Uuid::uuid4());
                $requestStack->push($request);

                return $requestStack;
            })(clone $requestStack, $headers, clone $request),
            (function ($httpClient, $reflectedHttpOptions, $requestStack) {
                $this->assertInstanceOf(HttpClient::class, $httpClient);
                $this->assertArrayHasKey('correlation-id', $reflectedHttpOptions['normalized_headers']);
                $this->assertEquals('Correlation-Id: '.$requestStack->getCurrentRequest()->headers->get(CorrelationId::HEADER_KEY),
                    $reflectedHttpOptions['normalized_headers']['correlation-id'][0]);
            }),
        ];

        yield 'request-without-correlation-id' => [
            (function ($requestStack, $headers, $request) {
                $request->headers->add($headers);
                $requestStack->push($request);

                return $requestStack;
            })(clone $requestStack, $headers, clone $request),
            (function ($httpClient, $reflectedHttpOptions, $requestStack) {
                $this->assertInstanceOf(HttpClient::class, $httpClient);
                $this->assertArrayHasKey('correlation-id', $reflectedHttpOptions['normalized_headers']);
                $this->assertArrayNotHasKey('correlation-id', $requestStack->getCurrentRequest()->headers->all());
            }),
        ];
    }
}
