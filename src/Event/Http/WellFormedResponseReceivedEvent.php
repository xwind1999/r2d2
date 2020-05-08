<?php

declare(strict_types=1);

namespace App\Event\Http;

use App\Event\AbstractLoggableEvent;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WellFormedResponseReceivedEvent extends AbstractLoggableEvent
{
    protected const LOG_MESSAGE = 'Correctly formed response received';

    protected string $clientId;

    protected string $method;

    protected string $uri;

    protected array $options;

    protected float $duration;

    protected ResponseInterface $response;

    public function __construct(string $clientId, string $method, string $uri, array $options, float $duration, ResponseInterface $response)
    {
        $this->clientId = $clientId;
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
        $this->duration = $duration;
        $this->response = $response;
    }

    public function getContext(): array
    {
        return [
            'request' => [
                'client' => $this->clientId,
                'method' => $this->method,
                'uri' => $this->uri,
                'options' => $this->options,
                'duration' => $this->duration,
            ],
            'response' => [
                'headers' => $this->response->getHeaders(false),
                'status_code' => $this->response->getStatusCode(),
                'location' => $this->response->getInfo('url'),
                'body' => $this->response->getContent(false),
            ],
        ];
    }
}
