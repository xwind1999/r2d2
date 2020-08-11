<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\ProductBoxImportCommand;

/**
 * @coversDefaultClass \App\Command\Import\AbstractProductImportCommand
 * @group product-import-command
 */
class ProductBoxImportCommandTest extends AbstractProductImportCommandTest
{
    protected string $commandName = 'r2d2:product-box:import';

    protected string $commandDescription = 'Command to push CSV product-box to the queue';

    protected function initCommand()
    {
        $this->command = new ProductBoxImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
    }

    public function requestProvider(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '1304',
                'sellableBrand' => 'SBX',
                'sellableCountry' => 'FR',
                'status' => 'ready',
                'listPrice.amount' => '30.00',
                'listPrice.currencyCode' => 'EUR',
                'universe.id' => 'STA',
                'type' => 'mev',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '1304',
                'sellableBrand' => 'AAAAAAAAA',
                'sellableCountry' => 'FR',
                'status' => 'ready',
                'listPrice.amount' => '30.00',
                'listPrice.currencyCode' => 'EUR',
                'universe.id' => 'STA',
                'type' => 'mev',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$records];
    }
}
