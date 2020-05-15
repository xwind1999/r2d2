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
                'Id' => '1304',
                'Name' => 'My nice little box',
                'Description' => 'desc',
                'Universe' => 'MTT',
                'IsSellable' => true,
                'IsReservable' => true,
                'Partner' => '32',
                'SellableBrand' => 'SBX',
                'SellableCountry' => 'FR',
                'Status' => 'ready',
                'Type' => 'mev',
                'ProductPeopleNumber' => 1,
                'VoucherExpirationDuration' => 36,
            ],
        ]);

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'Id' => '10386',
                'Name' => 'Le Château Le Verdoyer vous ac',
                'Description' => '',
                'Universe' => 'ABCDE',
                'IsSellable' => false,
                'IsReservable' => false,
                'Partner' => '17785',
                'SellableBrand' => 'BON',
                'SellableCountry' => 'BE',
                'Status' => 'active',
                'Type' => 'Experience',
                'ProductPeopleNumber' => 2,
                'VoucherExpirationDuration' => 0,
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
