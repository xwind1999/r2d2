<?php

declare(strict_types=1);

namespace App\Helper\Request;

use App\Exception\Http\UnprocessableEntityException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UuidValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return is_a((string) $argument->getType(), UuidInterface::class, true);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        try {
            $uuidString = $request->get($argument->getName(), null);
            if ($argument->isNullable() && null === $uuidString) {
                return [null];
            }

            $uuid = Uuid::fromString($uuidString);
        } catch (\Exception $e) {
            throw new UnprocessableEntityException();
        }

        return [$uuid];
    }
}
