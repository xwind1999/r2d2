<?php

declare(strict_types=1);

namespace App\Helper\Feature;

class AvailabilityConvertFlag implements FeatureInterface
{
    private bool $isEnabled;

    public function __construct(bool $isFeatureEnabled)
    {
        $this->isEnabled = $isFeatureEnabled;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
