<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\BoxManager;
use App\Manager\ExperienceManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PriceInformationBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private ExperienceManager $experienceManager;
    private BoxManager $boxManager;

    public function __construct(LoggerInterface $logger, ExperienceManager $experienceManager, BoxManager $boxManager)
    {
        $this->logger = $logger;
        $this->experienceManager = $experienceManager;
        $this->boxManager = $boxManager;
    }

    public function __invoke(PriceInformationRequest $priceInformationRequest): void
    {
        try {
            $this->experienceManager->insertPriceInfo($priceInformationRequest);
        } catch (ExperienceNotFoundException $exception) {
            $this->boxManager->insertPriceInfo($priceInformationRequest);
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), $priceInformationRequest->getContext());

            throw $exception;
        }
    }
}
