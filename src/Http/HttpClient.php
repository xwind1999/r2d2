<?php

declare(strict_types=1);

namespace App\Http;

use App\Event\Http\BadResponseReceivedEvent;
use App\Event\Http\ExternalServiceRequestMadeEvent;
use App\Event\Http\WellFormedResponseReceivedEvent;
use App\Exception\HttpClient\ConnectException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClient
{
    protected string $clientId;

    protected EventDispatcherInterface $dispatcher;

    protected HttpClientInterface $httpClient;

    public function __construct(string $clientId, EventDispatcherInterface $dispatcher, HttpClientInterface $httpClient)
    {
        $this->clientId = $clientId;
        $this->dispatcher = $dispatcher;
        $this->httpClient = $httpClient;
    }

    public function request(string $method, string $uri, array $query = [], array $body = [], array $headers = []): ResponseInterface
    {
        $options = [];

        if ($body) {
            $options['body'] = $body;
        }

        if ($query) {
            $options['query'] = $query;
        }

        if ($headers) {
            $options['headers'] = $headers;
        }

        $this->dispatcher->dispatch(new ExternalServiceRequestMadeEvent($this->clientId, $method, $uri, $options));
        $startTime = microtime(true);

        try {
            $response = $this->httpClient->request($method, $uri, $options);
            $response->getContent();
            $duration = microtime(true) - $startTime;

            $this->dispatcher->dispatch(new WellFormedResponseReceivedEvent($this->clientId, $method, $uri, $options, $duration, $response));

            return $response;
        } catch (HttpExceptionInterface $exception) {
            $response = $exception->getResponse();
            $duration = microtime(true) - $startTime;
            $this->dispatcher->dispatch(new BadResponseReceivedEvent($this->clientId, $method, $uri, $options, $duration, $response));

            throw $exception;
        } catch (\Throwable $exception) {
            $duration = microtime(true) - $startTime;
            $this->dispatcher->dispatch(new BadResponseReceivedEvent($this->clientId, $method, $uri, $options, $duration, null));

            throw ConnectException::forContext([], $exception);
        }
    }
}
