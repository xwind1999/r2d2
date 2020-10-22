<?php

declare(strict_types=1);

namespace App\Tests\Helper\Request;

use App\Exception\Http\BadRequestException;
use App\Exception\Http\InternalServerErrorException;
use App\Exception\Http\UnprocessableEntityException;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\RequestQueryInterface;
use App\Helper\Request\RequestQueryResolver;
use App\Helper\Request\ValidatableRequest;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\ArrayTransformerInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestQueryResolverTest extends ProphecyTestCase
{
    /**
     * @var ArrayTransformerInterface|ObjectProphecy
     */
    protected ObjectProphecy $serializer;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    protected ObjectProphecy $validator;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
    }

    /**
     * @dataProvider providerSupports
     */
    public function testSupports(bool $supports, ?object $class): void
    {
        $request = new Request();
        $argumentMetadata = new ArgumentMetadata('test', $class ? get_class($class) : null, false, false, null);

        $requestQueryResolver = new RequestQueryResolver($this->serializer->reveal(), $this->validator->reveal());

        $this->assertEquals($supports, $requestQueryResolver->supports($request, $argumentMetadata));
    }

    public function testResolve(): void
    {
        $request = new Request(['aa' => 'bb']);
        $class = new class() implements RequestQueryInterface, ValidatableRequest {
        };
        $argumentMetadata = new ArgumentMetadata('test', get_class($class), false, false, null);
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);

        $this->serializer->fromArray(['aa' => 'bb'], get_class($class))->willReturn($class)->shouldBeCalled();
        $this->validator->validate($class)->willReturn([]);

        $requestQueryResolver = new RequestQueryResolver($this->serializer->reveal(), $this->validator->reveal());

        $resolved = $requestQueryResolver->resolve($request, $argumentMetadata);
        $this->assertEquals([$class], $resolved);
    }

    public function testResolveWithInvalidType(): void
    {
        $request = new Request([]);
        $this->expectException(InternalServerErrorException::class);

        $argumentMetadata = new ArgumentMetadata('test', null, false, false, null);

        $requestQueryResolver = new RequestQueryResolver($this->serializer->reveal(), $this->validator->reveal());

        $requestQueryResolver->resolve($request, $argumentMetadata);
    }

    public function testResolveWithInvalidData(): void
    {
        $request = new Request(['aa' => 'bb']);
        $this->expectException(BadRequestException::class);
        $class = new class() {
        };

        $argumentMetadata = new ArgumentMetadata('test', get_class($class), false, false, null);

        $requestQueryResolver = new RequestQueryResolver($this->serializer->reveal(), $this->validator->reveal());
        $this->serializer->fromArray(['aa' => 'bb'], get_class($class))->willThrow(new \RuntimeException());

        $requestQueryResolver->resolve($request, $argumentMetadata);
    }

    public function testResolveValidationWillFail(): void
    {
        $request = new Request(['aa' => 'bb']);
        $this->expectException(UnprocessableEntityException::class);
        $class = new class() implements RequestBodyInterface, ValidatableRequest {
        };
        $argumentMetadata = new ArgumentMetadata('test', get_class($class), false, false, null);
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);

        $violation = new ConstraintViolation('this validation failed', null, [], '', 'message', 'xxx');
        $this->serializer->fromArray(['aa' => 'bb'], get_class($class))->willReturn($class)->shouldBeCalled();
        $this->validator->validate($class)->willReturn([$violation]);

        $requestQueryResolver = new RequestQueryResolver($this->serializer->reveal(), $this->validator->reveal());

        $requestQueryResolver->resolve($request, $argumentMetadata);
    }

    /**
     * @see testSupports
     */
    public function providerSupports(): iterable
    {
        yield 'random class that doesnt support it' => [false, new class() {
        }];
        yield 'no class' => [false, null];
        yield 'proper class that supports it' => [true, new class() implements RequestQueryInterface, ValidatableRequest {
        }];
    }
}
