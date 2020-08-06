<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\RoomAvailabilityImportCommand;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \App\Command\Import\RoomAvailabilityImportCommand
 */
class RoomAvailabilityImportCommandTest extends AbstractImportCommandTest
{
    /**
     * @var ObjectProphecy|RoomAvailabilityRequest
     */
    protected ObjectProphecy $requestClass;

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(RoomAvailabilityRequest::class);
        parent::setUp();
        $application = new Application(static::createKernel());

        $this->command = new RoomAvailabilityImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function requestProvider(): \Generator
    {
        $iterator = new \ArrayIterator([
            [
                'product.id' => '1234567',
                'quantity' => 1,
                'dateFrom' => '2020-01-01',
                'dateTo' => '2020-01-02',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$iterator];

        $iterator = new \ArrayIterator([
            [
                'product.id' => '1234567',
                'quantity' => 1,
                'dateFrom' => '2020-01-01',
                'dateTo' => '2020-01-02',
                'updatedAt' => null,
            ],
        ]);

        yield [$iterator];
    }

    public function requestProviderInvalidData(): \Generator
    {
        $iterator = new \ArrayIterator([
            [
                'product.id' => '1234567',
                'quantity' => null,
                'dateFrom' => '2020-01-02',
                'dateTo' => '2020-01-02',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$iterator];
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     *
     * @dataProvider requestProvider
     */
    public function testExecuteSuccessfully(\ArrayIterator $arrayRoomAvailabilityRequest): void
    {
        $this->executeWithValidData($arrayRoomAvailabilityRequest);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('r2d2:room-availability:import', $this->command::getDefaultName());
        $this->assertStringContainsString('[OK] Command executed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Total records: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at:', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at :', $this->commandTester->getDisplay());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers ::logError
     *
     * @dataProvider requestProviderInvalidData
     */
    public function testExecuteWithInvalidData(\ArrayIterator $arrayRoomAvailabilityRequest): void
    {
        $this->executeWithInvalidData($arrayRoomAvailabilityRequest);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers ::logError
     *
     * @dataProvider requestProvider
     */
    public function testExecuteCatchesException(\ArrayIterator $arrayRoomAvailabilityRequest): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($arrayRoomAvailabilityRequest);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'RoomAvailabilities_Test.csv',
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Total records: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at:', $this->commandTester->getDisplay());
        $this->assertStringContainsString('[ERROR] Command exited', $this->commandTester->getDisplay());
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to push CSV room-availability to the queue', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
