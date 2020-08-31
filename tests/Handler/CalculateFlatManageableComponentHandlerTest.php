<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Message\CalculateFlatManageableComponent;
use App\Handler\CalculateFlatManageableComponentHandler;
use App\Repository\Flat\FlatManageableComponentRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Handler\CalculateFlatManageableComponentHandler
 */
class CalculateFlatManageableComponentHandlerTest extends TestCase
{
    /**
     * @var FlatManageableComponentRepository|ObjectProphecy
     */
    private $repository;

    private CalculateFlatManageableComponentHandler $calculateFlatManageableComponentHandler;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(FlatManageableComponentRepository::class);
        $this->calculateFlatManageableComponentHandler = new CalculateFlatManageableComponentHandler($this->repository->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $this->repository->refreshComponent('1234')->shouldBeCalled();

        $event = new CalculateFlatManageableComponent('1234');
        $this->calculateFlatManageableComponentHandler->__invoke($event);
    }
}
