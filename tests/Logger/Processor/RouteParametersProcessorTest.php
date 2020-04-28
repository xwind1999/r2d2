<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\RouteParametersProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RouteParametersProcessorTest extends TestCase
{
    public function testAddInfo(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $request = $this->prophesize(Request::class);
        $request->getPathInfo()->willReturn('this-is-the-path');
        $request->query = new ParameterBag(['q1' => 'q2']);
        $request->attributes = new ParameterBag(['q3' => 'q4']);
        $requestStack->getMasterRequest()->willReturn($request->reveal());
        $processor = new RouteParametersProcessor($requestStack->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'route' => ['q3' => 'q4', 'query' => ['q1' => 'q2']]],
            $processor(['test' => 'test2'])
        );
    }

    public function testAddInfoWithoutRequest(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getMasterRequest()->willReturn(null);
        $processor = new RouteParametersProcessor($requestStack->reveal());
        $this->assertEquals(['test', 'test2'], $processor(['test', 'test2']));
    }
}
