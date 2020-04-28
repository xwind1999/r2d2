<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\PathInfoProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PathInfoProcessorTest extends TestCase
{
    public function testAddInfo(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $request = $this->prophesize(Request::class);
        $request->getPathInfo()->willReturn('this-is-the-path');
        $requestStack->getMasterRequest()->willReturn($request->reveal());
        $processor = new PathInfoProcessor($requestStack->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'path_info' => 'this-is-the-path'],
            $processor(['test' => 'test2'])
        );
    }

    public function testAddInfoWithoutRequest(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getMasterRequest()->willReturn(null);
        $processor = new PathInfoProcessor($requestStack->reveal());
        $this->assertEquals(['test', 'test2'], $processor(['test', 'test2']));
    }
}
