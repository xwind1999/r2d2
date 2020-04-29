<?php

declare(strict_types=1);

namespace App\Logger\Processor;

use Symfony\Component\HttpFoundation\RequestStack;

class RouteParametersProcessor
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(array $record): array
    {
        $request = $this->requestStack->getMasterRequest();

        if (!$request) {
            return $record;
        }

        $record['extra']['route'] = $request->attributes->all();
        $record['extra']['route']['query'] = $request->query->all();

        return $record;
    }
}
