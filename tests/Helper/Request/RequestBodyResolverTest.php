<?php

declare(strict_types=1);

namespace App\Tests\Helper\Request;

use App\Exception\Http\BadRequestException;
use App\Exception\Http\InternalServerErrorException;
use App\Exception\Http\UnprocessableEntityException;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\RequestBodyResolver;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBodyResolverTest extends TestCase
{
    /**
     * @var ObjectProphecy|SerializerInterface
     */
    protected ObjectProphecy $serializer;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    protected ObjectProphecy $validator;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
    }

    /**
     * @dataProvider providerSupports
     */
    public function testSupports(bool $supports, ?object $class): void
    {
        $request = new Request();
        $argumentMetadata = new ArgumentMetadata('test', $class ? get_class($class) : null, false, false, null);

        $requestBodyResolver = new RequestBodyResolver($this->serializer->reveal(), $this->validator->reveal());

        $this->assertEquals($supports, $requestBodyResolver->supports($request, $argumentMetadata));
    }

    public function testResolve(): void
    {
        $request = new Request([], [], [], [], [], [], 'aa');
        $class = new class() implements RequestBodyInterface, ValidatableRequest {
        };
        $argumentMetadata = new ArgumentMetadata('test', get_class($class), false, false, null);
        $this->serializer = $this->prophesize(SerializerInterface::class);

        $this->serializer->deserialize('aa', get_class($class), 'json')->willReturn($class)->shouldBeCalled();
        $this->validator->validate($class)->willReturn([]);

        $requestBodyResolver = new RequestBodyResolver($this->serializer->reveal(), $this->validator->reveal());

        $this->assertEquals([$class], $requestBodyResolver->resolve($request, $argumentMetadata));
    }

    public function testResolveWithInvalidType(): void
    {
        $request = new Request([], [], [], [], [], [], 'aa');
        $this->expectException(InternalServerErrorException::class);

        $argumentMetadata = new ArgumentMetadata('test', null, false, false, null);

        $requestBodyResolver = new RequestBodyResolver($this->serializer->reveal(), $this->validator->reveal());

        $requestBodyResolver->resolve($request, $argumentMetadata);
    }

    public function testResolveWithInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'aa');
        $this->expectException(BadRequestException::class);
        $class = new class() {
        };

        $argumentMetadata = new ArgumentMetadata('test', get_class($class), false, false, null);

        $requestBodyResolver = new RequestBodyResolver($this->serializer->reveal(), $this->validator->reveal());
        $this->serializer->deserialize('aa', get_class($class), 'json')->willThrow(new \RuntimeException());

        $requestBodyResolver->resolve($request, $argumentMetadata);
    }

    public function testResolveValidationWillFail(): void
    {
        $request = new Request([], [], [], [], [], [], 'aa');
        $this->expectException(UnprocessableEntityException::class);
        $class = new class() implements RequestBodyInterface, ValidatableRequest {
        };
        $argumentMetadata = new ArgumentMetadata('test', get_class($class), false, false, null);
        $this->serializer = $this->prophesize(SerializerInterface::class);

        $violation = new ConstraintViolation('this validation failed', null, [], '', 'message', 'xxx');
        $this->serializer->deserialize('aa', get_class($class), 'json')->willReturn($class)->shouldBeCalled();
        $this->validator->validate($class)->willReturn([$violation]);

        $requestBodyResolver = new RequestBodyResolver($this->serializer->reveal(), $this->validator->reveal());

        $requestBodyResolver->resolve($request, $argumentMetadata);
    }

    /**
     * @see testSupports
     */
    public function providerSupports(): iterable
    {
        yield 'random class that doesnt support it' => [false, new class() {
        }];
        yield 'no class' => [false, null];
        yield 'proper class that supports it' => [true, new class() implements RequestBodyInterface, ValidatableRequest {
        }];
    }
}
