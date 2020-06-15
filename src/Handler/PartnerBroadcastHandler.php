<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Exception\ContextualException;
use App\Manager\PartnerManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PartnerBroadcastHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private PartnerManager $partnerManager;

    public function __construct(LoggerInterface $logger, PartnerManager $partnerManager)
    {
        $this->logger = $logger;
        $this->partnerManager = $partnerManager;
    }

    public function __invoke(PartnerRequest $partnerRequest): void
    {
        try {
            $this->partnerManager->replace($partnerRequest);
        } catch (ContextualException $exception) {
            $this->logger->warning($exception, $partnerRequest->getContext());

            throw $exception;
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), $partnerRequest->getContext());

            throw $exception;
        }
    }
}
