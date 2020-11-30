<?php

declare(strict_types=1);

namespace App\Helper\Request;

use App\Exception\Http\BadRequestException;
use App\Exception\Http\InternalServerErrorException;
use App\Exception\Http\UnprocessableEntityException;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestQueryResolver implements ArgumentValueResolverInterface
{
    private ArrayTransformerInterface $serializer;

    private ValidatorInterface $validator;

    public function __construct(ArrayTransformerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $argumentType = $argument->getType();

        if (null === $argumentType || !class_exists($argumentType)) {
            return false;
        }

        $reflection = new \ReflectionClass($argumentType);

        return $reflection->implementsInterface(ValidatableRequest::class)
            && $reflection->implementsInterface(RequestQueryInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        try {
            $query = $request->query->all();
            $argumentType = $argument->getType();

            if (null === $argumentType || !class_exists($argumentType)) {
                throw new \Exception();
            }

            $data = $this->serializer->fromArray($query, $argumentType);
        } catch (\RuntimeException $exception) {
            throw new BadRequestException();
        } catch (\Throwable $exception) {
            throw new InternalServerErrorException();
        }

        try {
            $v = $this->validator->validate($data);
            if (count($v) > 0) {
                throw new ValidatorException();
            }
        } catch (ValidatorException $exception) {
            $exception = new UnprocessableEntityException();
            if (isset($v) && count($v) > 0) {
                $validationErrors = [];
                /** @var ConstraintViolation $failedValidation */
                foreach ($v as $failedValidation) {
                    $field = $failedValidation->getPropertyPath();
                    if (!isset($validationErrors[$field])) {
                        $validationErrors[$field] = [];
                    }
                    $validationErrors[$field][] = $failedValidation->getMessage();
                }
                $exception->addContext(['errors' => $validationErrors]);
            }

            throw $exception;
        }

        return [$data];
    }
}
