<?php

declare(strict_types=1);

namespace App\Event\Http;

use App\Event\AbstractLoggableEvent;

class ExternalServiceRequestMadeEvent extends AbstractLoggableEvent
{
    protected const MESSAGE = 'Request made to external service';

    protected string $clientId;

    protected string $method;

    protected string $uri;

    protected array $options;

    public function __construct(string $clientId, string $method, string $uri, array $options)
    {
        $this->clientId = $clientId;
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
    }

    public function getContext(): array
    {
        return [
            'request' => [
                'client' => $this->clientId,
                'method' => $this->method,
                'uri' => $this->uri,
                'options' => $this->options,
            ],
        ];
    }
}
