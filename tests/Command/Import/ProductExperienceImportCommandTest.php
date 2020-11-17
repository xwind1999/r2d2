<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\ProductExperienceImportCommand;

/**
 * @coversDefaultClass \App\Command\Import\AbstractProductImportCommand
 * @group product-import-command
 */
class ProductExperienceImportCommandTest extends AbstractProductImportCommandTest
{
    protected string $commandName = 'r2d2:product-experience:import';

    protected string $commandDescription = 'Command to push CSV product-experience to the queue';

    protected function initCommand()
    {
        $this->command = new ProductExperienceImportCommand(
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
                'type' => 'experience',
                'status' => 'ready',
                'productPeopleNumber' => '1',
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
                'type' => 'experience',
                'status' => 'ready',
                'productPeopleNumber' => 'bbbbb',
                'updatedAt' => 'aaaa-bb--c',
            ],
        ]);

        yield [$records];
    }
}
