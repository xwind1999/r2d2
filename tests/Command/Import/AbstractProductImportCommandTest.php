<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Contract\Request\BroadcastListener\ProductRequest;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractProductImportCommandTest extends AbstractImportCommandTest
{
    protected ObjectProphecy $requestClass;

    protected string $commandName = 'r2d2:product:import';

    protected string $commandDescription = 'Command to push CSV product to the queue';

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(ProductRequest::class);
        parent::setUp();

        $this->initCommand();

        $this->commandTester = new CommandTester($this->command);
        $this->application->add($this->command);
    }

    abstract protected function initCommand();

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::fromArray
     * @covers \App\Contract\Request\BroadcastListener\Product\ListPrice::createFromAmountAndCurrencyCode
     * @dataProvider requestProviderInvalidData
     */
    public function testExecuteWithInvalidData(\Iterator $productRequests): void
    {
        $this->executeWithInvalidData($productRequests);

        $this->assertEquals($this->commandName, $this->command::getDefaultName());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @dataProvider requestProvider
     */
    public function testExecuteWithValidData(\Iterator $productRequests): void
    {
        $this->executeWithValidData($productRequests);

        $this->assertEquals($this->commandName, $this->command::getDefaultName());
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals($this->commandDescription, $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
