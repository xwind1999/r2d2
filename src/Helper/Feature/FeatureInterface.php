<?php

declare(strict_types=1);

namespace App\Helper\Feature;

interface FeatureInterface
{
    public function isEnabled(): bool;
}
