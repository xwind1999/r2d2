<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\ProductImportCommand;
use App\Contract\Request\BroadcastListener\ProductRequest;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \App\Command\Import\ProductImportCommand
 * @group product-import-command
 */
class ProductImportCommandTest extends AbstractImportCommandTest
{
    protected ObjectProphecy $requestClass;

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(ProductRequest::class);
        parent::setUp();

        $this->command = new ProductImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal());

        $this->commandTester = new CommandTester($this->command);
        $this->application->add($this->command);
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @dataProvider requestProviderInvalidData
     */
    public function testExecuteWithInvalidData(\Iterator $productRequests): void
    {
        $this->executeWithInvalidData($productRequests);

        $this->assertEquals('r2d2:product:import', $this->command::getDefaultName());
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

        $this->assertEquals('r2d2:product:import', $this->command::getDefaultName());
    }

    public function requestProvider(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '1304',
                'name' => 'My nice little box',
                'description' => 'desc',
                'universe' => 'MTT',
                'sellable_brand' => 'SBX',
                'sellable_country' => 'FR',
                'partner' => '32',
                'status' => 'ready',
                'type' => 'mev',
                'product_people_number' => 1,
                'is_sellable' => true,
                'is_reservable' => true,
                'voucher_expiration_duration' => 36,
            ],
        ]);

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '10386',
                'name' => 'Le Château Le Verdoyer vous ac',
                'description' => '',
                'universe' => 'ABCDE',
                'partner' => '17785',
                'sellable_brand' => 'BON',
                'sellable_country' => 'BE',
                'status' => 'active',
                'type' => 'Experience',
                'product_people_number' => 2,
                'is_sellable' => false,
                'is_reservable' => false,
                'voucher_expiration_duration' => 0,
            ],
        ]);

        yield [$records];
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to push CSV product to the queue', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
