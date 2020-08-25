<?php

declare(strict_types=1);

namespace App\HealthCheck;

use App\QuickData\QuickData;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\AbstractResult;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

class QuickDataCheck extends AbstractCheck
{
    protected const EXPERIENCE_ID = '2501';

    protected QuickData $quickData;

    public function __construct(QuickData $quickData)
    {
        $this->quickData = $quickData;
    }

    public function check(): ResultInterface
    {
        try {
            return $this->validateQuickData();
        } catch (\Throwable $exc) {
            return new Failure('Unable to contact QuickData!');
        }
    }

    private function validateQuickData(): AbstractResult
    {
        $startDate = new \DateTime('+10 day');
        $endDate = new \DateTime('+15 day');

        $getPackage = $this->quickData->getPackage(self::EXPERIENCE_ID, $startDate, $endDate);

        if (empty($getPackage['ListPrestation'][0]['PartnerCode'])) {
            return new Failure('GetPackage is missing the PartnerCode');
        }

        return new Success();
    }
}
