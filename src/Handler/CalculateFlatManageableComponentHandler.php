<?php

declare(strict_types=1);

namespace App\Handler;

use App\Contract\Message\CalculateFlatManageableComponent;
use App\Repository\Flat\FlatManageableComponentRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CalculateFlatManageableComponentHandler implements MessageHandlerInterface
{
    private FlatManageableComponentRepository $flatManageableComponentRepository;

    public function __construct(
        FlatManageableComponentRepository $flatManageableComponentRepository
    ) {
        $this->flatManageableComponentRepository = $flatManageableComponentRepository;
    }

    public function __invoke(CalculateFlatManageableComponent $calculateFlatManageableComponent): void
    {
        $this->flatManageableComponentRepository->refreshComponent($calculateFlatManageableComponent->componentGoldenId);
    }
}
