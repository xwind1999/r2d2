<?php

declare(strict_types=1);

namespace App\HealthCheck;

use App\CMHub\CMHub;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\AbstractResult;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Symfony\Component\HttpFoundation\Response;

class CMHubCheck extends AbstractCheck
{
    protected const PRODUCT_ID = 286201;

    protected CMHub $cmHub;

    public function __construct(CMHub $cmHub)
    {
        $this->cmHub = $cmHub;
    }

    public function check(): ResultInterface
    {
        try {
            return $this->validateCMHub();
        } catch (\Throwable $exc) {
            return new Failure('Unable to contact CMHub!');
        }
    }

    private function validateCMHub(): AbstractResult
    {
        $startDate = new \DateTime('-2 day');
        $endDate = new \DateTime();

        $getAvailability = $this->cmHub->getAvailability(self::PRODUCT_ID, $startDate, $endDate);

        if (Response::HTTP_OK !== $getAvailability->getStatusCode()) {
            return new Failure('GetAvailability has failed.');
        }

        return new Success();
    }
}
