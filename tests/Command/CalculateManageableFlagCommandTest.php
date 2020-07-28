<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CalculateManageableFlagCommand;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Entity\Component;
use App\Exception\Repository\ComponentNotFoundException;
use App\Helper\CSVParser;
use App\Repository\ComponentRepository;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Command\CalculateManageableFlagCommand
 */
class CalculateManageableFlagCommandTest extends KernelTestCase
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

    /**
     * @var ManageableProductRequest|ObjectProphecy
     */
    private $manageableProductRequest;

    private Application $application;
    private Command $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->csvParser = $this->prophesize(CSVParser::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->manageableProductRequest = $this->prophesize(ManageableProductRequest::class);
        $this->application = new Application(static::createKernel());
        $this->command = new CalculateManageableFlagCommand(
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
        $this->componentRepository->findListByGoldenId(Argument::any())->shouldBeCalledOnce()->willReturn($components);
        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->messageBus
            ->dispatch(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn(new Envelope(new \stdClass()))
        ;
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '1',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Total CSV IDs received: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Total Collection IDs read: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Command executed', $this->commandTester->getDisplay());
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
        $this->manageableProductRequest->setProductRequest(Argument::any())->shouldNotBeCalled();
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
        $this->componentRepository->findListByGoldenId(Argument::any())->shouldBeCalledOnce()->willReturn($components);
        $this->logger->error(Argument::any())->shouldBeCalledOnce();
        $this->messageBus
            ->dispatch(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(\Exception::class)
        ;
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '1',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Command executed', $this->commandTester->getDisplay());
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
                    'golden_id' => [
                        '561060698188',
                    ],
                ],
            ]
        );
        $component = $this->prophesize(Component::class);
        $component->goldenId = '561060698188';
        $component->name = 'name';
        $component->status = 'active';
        $component->reveal();
        $components = [
            0 => $component,
        ];

        yield [
            $goldenIds,
            $components,
        ];
    }
}
