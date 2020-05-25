<?php

declare(strict_types=1);

namespace App\Helper\Request;

use App\Exception\Http\BadRequestException;
use App\Exception\Http\InternalServerErrorException;
use App\Exception\Http\UnprocessableEntityException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBodyResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;

    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
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
            && $reflection->implementsInterface(RequestBodyInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        try {
            $json = $request->getContent();
            $argumentType = $argument->getType();

            if (null === $argumentType || !class_exists($argumentType) || is_resource($json)) {
                throw new \Exception();
            }

            $data = $this->serializer->deserialize($json, $argumentType, 'json');
        } catch (\TypeError $exception) {
            throw new UnprocessableEntityException();
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
