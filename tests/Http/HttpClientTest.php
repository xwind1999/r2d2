<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\MalformedResponseReceivedEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use App\Exception\HttpClient\ConnectException;
use App\Http\HttpClient;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientTest extends TestCase
{
    protected string $clientId = 'client';

    /**
     * @var EventDispatcherInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $dispatcher;

    /**
     * @var HttpClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $httpClientInterface;

    protected HttpClient $httpClient;

    public function setUp(): void
    {
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->httpClientInterface = $this->prophesize(HttpClientInterface::class);
        $this->httpClient = new HttpClient($this->clientId, $this->dispatcher->reveal(), $this->httpClientInterface->reveal());
    }

    public function testRequest()
    {
        $method = 'GET';
        $uri = '/test-uri';
        $query = ['query-field' => 'field1'];
        $body = ['body-field' => 'field2'];
        $headers = ['X-Header' => 'value'];

        $this->dispatcher->dispatch(Argument::type(ExternalServiceRequestMadeEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(Argument::type(WellFormedResponseReceivedEvent::class))->shouldBeCalled();

        $responseInterface = $this->prophesize(ResponseInterface::class);
        $responseInterface->getContent()->shouldBeCalled();
        $this->httpClientInterface->request($method, $uri, Argument::type('array'))->willReturn($responseInterface->reveal());
        $response = $this->httpClient->request($method, $uri, $query, $body, $headers);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRequestWithHttpError()
    {
        $method = 'GET';
        $uri = '/test-uri';
        $query = ['query-field' => 'field1'];
        $body = ['body-field' => 'field2'];
        $headers = ['X-Header' => 'value'];

        $this->dispatcher->dispatch(Argument::type(ExternalServiceRequestMadeEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(Argument::type(MalformedResponseReceivedEvent::class))->shouldBeCalled();

        $exception = $this->prophesize(HttpExceptionInterface::class);

        $responseInterface = $this->prophesize(ResponseInterface::class);

        $exception->getResponse()->willReturn($responseInterface->reveal());

        $this->httpClientInterface->request($method, $uri, Argument::type('array'))->willThrow($exception->reveal());

        $this->expectException(HttpExceptionInterface::class);
        $this->httpClient->request($method, $uri, $query, $body, $headers);
    }

    public function testRequestWithAnyOtherError()
    {
        $method = 'GET';
        $uri = '/test-uri';
        $query = ['query-field' => 'field1'];
        $body = ['body-field' => 'field2'];
        $headers = ['X-Header' => 'value'];

        $this->dispatcher->dispatch(Argument::type(ExternalServiceRequestMadeEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(Argument::type(MalformedResponseReceivedEvent::class))->shouldBeCalled();

        $this->httpClientInterface->request($method, $uri, Argument::type('array'))->willThrow(new \Exception());

        $this->expectException(ConnectException::class);
        $this->httpClient->request($method, $uri, $query, $body, $headers);
    }
}
