<?php

declare(strict_types=1);

namespace App\Tests\Helper\Request;

use App\Exception\Http\UnprocessableEntityException;
use App\Helper\Request\UuidValueResolver;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UuidValueResolverTest extends TestCase
{
    /**
     * @dataProvider providerSupports
     */
    public function testSupports(bool $supports, ?string $class, bool $nullable): void
    {
        $request = new Request();
        $argumentMetadata = new ArgumentMetadata('test', $class ?? null, false, false, null, $nullable);

        $requestBodyResolver = new UuidValueResolver();

        $this->assertEquals($supports, $requestBodyResolver->supports($request, $argumentMetadata));
    }

    public function testResolve(): void
    {
        $request = new Request(['test' => 'eedc7cbe-5328-11ea-8d77-2e728ce88125'], [], [], [], [], [], 'aa');
        $argumentMetadata = new ArgumentMetadata('test', Uuid::class, false, false, null);

        $requestBodyResolver = new UuidValueResolver();

        $this->assertInstanceOf(Uuid::class, $requestBodyResolver->resolve($request, $argumentMetadata)[0]);
    }

    public function testResolveNullable(): void
    {
        $request = new Request([], [], [], [], [], [], 'aa');
        $argumentMetadata = new ArgumentMetadata('test', Uuid::class, false, false, null, true);

        $requestBodyResolver = new UuidValueResolver();

        $this->assertNull($requestBodyResolver->resolve($request, $argumentMetadata)[0]);
    }

    public function testResolveWithInvalidInput(): void
    {
        $request = new Request(['test' => 'unfortunately-this-is-not-valid'], [], [], [], [], [], 'aa');
        $argumentMetadata = new ArgumentMetadata('test', Uuid::class, false, false, null, true);

        $requestBodyResolver = new UuidValueResolver();
        $this->expectException(UnprocessableEntityException::class);
        $requestBodyResolver->resolve($request, $argumentMetadata);
    }

    /**
     * @see testSupports
     */
    public function providerSupports(): iterable
    {
        yield 'random class that doesnt support it' => [false, \Exception::class, false];
        yield 'no class' => [false, null, false];
        yield 'proper class that supports it' => [true, UuidInterface::class, false];
        yield 'random class that doesnt support it, but nullable' => [false, \Exception::class, true];
        yield 'no class, but nullable' => [false, null, true];
        yield 'proper class that supports it, but nullable' => [true, Uuid::class, true];
    }
}
