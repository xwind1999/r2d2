<?php

declare(strict_types=1);

namespace App\Logger\Processor;

class EnvironmentNameProcessor
{
    private string $environmentName;

    public function __construct(string $environmentName)
    {
        $this->environmentName = $environmentName;
    }

    public function __invoke(array $record): array
    {
        $record['extra']['environment_name'] = $this->environmentName;

        return $record;
    }
}
