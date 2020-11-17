<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\ProductComponentImportCommand;

/**
 * @coversDefaultClass \App\Command\Import\AbstractProductImportCommand
 * @group product-import-command
 */
class ProductComponentImportCommandTest extends AbstractProductImportCommandTest
{
    protected string $commandName = 'r2d2:product-component:import';

    protected string $commandDescription = 'Command to push CSV product-component to the queue';

    protected function initCommand()
    {
        $this->command = new ProductComponentImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal(),
            $this->serializer->reveal(),
            $this->moneyHelper->reveal()
        );
    }

    public function requestProvider(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '1304',
                'partner' => '32',
                'name' => 'My nice little box',
                'description' => 'desc',
                'stockAllotment' => '1',
                'productDuration' => '1',
                'productDurationUnit' => 'day',
                'roomStockType' => 'on_request',
                'isSellable' => true,
                'isReservable' => true,
                'status' => 'ready',
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
                'partner' => '32',
                'name' => 'My nice little box',
                'description' => 'desc',
                'stockAllotment' => 'cxccs ddddd',
                'productDuration' => 'aaaa bbb',
                'productDurationUnit' => 'day',
                'roomStockType' => 'on_request',
                'isSellable' => true,
                'isReservable' => true,
                'status' => 'ready',
                'type' => 'mev',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$records];
    }
}
