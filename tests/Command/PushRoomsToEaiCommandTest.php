<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\PushRoomsToEaiCommand;
use App\Entity\Component;
use App\Entity\Partner;
use App\Exception\Repository\ComponentNotFoundException;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use App\Tests\ProphecyKernelTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Command\PushRoomsToEaiCommand
 */
class PushRoomsToEaiCommandTest extends ProphecyKernelTestCase
{
    /**
     * @var CSVParser|ObjectProphecy
     */
    private ObjectProphecy $csvParser;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private ObjectProphecy $logger;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private ObjectProphecy $messageBus;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    private $componentRepository;

    private Application $application;
    private Command $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->csvParser = $this->prophesize(CSVParser::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->application = new Application(static::createKernel());
        $this->command = new PushRoomsToEaiCommand(
            $this->csvParser->reveal(),
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->componentRepository->reveal()
        );
        $this->commandTester = new CommandTester($this->command);
        $this->application->add($this->command);
    }

    /**
     * @cover ::execute
     * @cover ::transformFromIterator
     * @cover ::processComponents
     * @dataProvider componentsAndIdsProvider
     */
    public function testExecuteSuccessfully(\Iterator $goldenIds, array $components): void
    {
        $this->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
        $this->componentRepository->findListByGoldenId(Argument::any())->shouldBeCalledTimes(2)->willReturn($components);
        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->messageBus
            ->dispatch(Argument::any())
            ->shouldBeCalledTimes(2)
            ->willReturn(new Envelope(new \stdClass()))
        ;
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '2',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Total CSV IDs received: 2', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Total Collection IDs read: 2', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Command executed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at: ', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at : ', $this->commandTester->getDisplay());
    }

    /**
     * @cover ::execute
     * @cover ::transformFromIterator
     * @cover ::processComponents
     * @dataProvider componentsAndIdsProvider
     */
    public function testExecuteThrowsComponentNotFoundException(\Iterator $goldenIds, array $components): void
    {
        $this->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
        $this->componentRepository
            ->findListByGoldenId(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(ComponentNotFoundException::class)
        ;
        $this->logger->error(Argument::any())->shouldBeCalledOnce();
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->logger->error(Argument::any())->shouldBeCalledOnce();
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '1',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    /**
     * @cover ::execute
     * @cover ::transformFromIterator
     * @cover ::processComponents
     * @dataProvider componentsAndIdsProvider
     */
    public function testExecuteCatchesException(\Iterator $goldenIds, array $components): void
    {
        $this->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
        $this->componentRepository->findListByGoldenId(Argument::any())->shouldBeCalledTimes(2)->willReturn($components);
        $this->logger->error(Argument::any())->shouldBeCalledTimes(2);
        $this->messageBus
            ->dispatch(Argument::any())
            ->shouldBeCalledTimes(2)
            ->willThrow(\Exception::class)
        ;
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '2',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Command executed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at: ', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at : ', $this->commandTester->getDisplay());
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to send manageable products (components) to EAI', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
        $this->assertEquals('BATCH SIZE', $definition->getArgument('batchSize')->getDescription());
        $this->assertEquals('r2d2:eai:push-rooms', $this->command::getDefaultName());
    }

    /**
     * @see testExecuteSuccessfully
     * @see testExecuteThrowsComponentNotFoundException
     */
    public function componentsAndIdsProvider(): \Generator
    {
        $goldenIds = new \ArrayIterator(
            [
                [
                    'golden_id' => '561060698188',
                ],
                [
                    'golden_id' => '111111111',
                ],
            ],
        );
        $component = new Component();
        $component->goldenId = '561060698188';
        $component->name = 'name';
        $component->status = 'active';
        $component->isSellable = true;
        $component->isReservable = true;
        $partner = new Partner();
        $partner->goldenId = '123';
        $component->partner = $partner;
        $components = [
            1 => $component,
        ];

        yield [
            $goldenIds,
            $components,
        ];
    }
}
