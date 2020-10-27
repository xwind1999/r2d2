<?php

declare(strict_types=1);

namespace App\HealthCheck;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Smartbox\ApiRestClient\Clients\ChecksV0Client;
use Symfony\Component\HttpFoundation\Request;

class EAICheck extends AbstractCheck
{
    private ChecksV0Client $client;

    public function __construct(ChecksV0Client $client)
    {
        $this->client = $client;
    }

    public function check()
    {
        try {
            return $this->validateEai();
        } catch (\Throwable $exc) {
            return new Failure('Unable to contact EAI!');
        }
    }

    private function validateEai(): ResultInterface
    {
        $request = new Request();
        $request->request->add(['data' => ['_format' => 'json']]);

        $response = $this->client->void(
            [$request],
            ['Accept' => 'application/json']
        );

        if ($response->getStatusCode() >= 300) {
            return new Failure(sprintf('Unable to contact EAI! Status code: %s', $response->getStatusCode()));
        }

        return new Success(sprintf('Response status code: %s', $response->getStatusCode()));
    }
}
