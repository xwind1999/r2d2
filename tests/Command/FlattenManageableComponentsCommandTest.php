<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\FlattenManageableComponentsCommand;
use App\Contract\Message\CalculateFlatManageableComponent;
use App\Repository\ComponentRepository;
use App\Tests\ProphecyKernelTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class FlattenManageableComponentsCommandTest extends ProphecyKernelTestCase
{
    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var ComponentRepository|ObjectProphecy
     */
    private $componentRepository;

    private CommandTester $commandTester;

    private FlattenManageableComponentsCommand $command;

    public function setUp(): void
    {
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->componentRepository = $this->prophesize(ComponentRepository::class);
        $this->command = new FlattenManageableComponentsCommand(
            $this->messageBus->reveal(),
            $this->componentRepository->reveal()
        );

        $this->commandTester = new CommandTester($this->command);
        (new Application(static::createKernel()))->add($this->command);
    }

    public function testExecute()
    {
        $this->componentRepository->getAllManageableGoldenIds()->willReturn(['12345', '67890']);

        $this
            ->messageBus
            ->dispatch(Argument::type(CalculateFlatManageableComponent::class))
            ->shouldBeCalledTimes(2)
            ->willReturn(new Envelope(new \stdClass()));

        $this->commandTester->execute(['command' => 'r2d2:flatten:manageable-components']);
        $this->commandTester->getDisplay();
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('', $this->commandTester->getDisplay());
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to populate the flat manageable components table', $this->command->getDescription());
        $this->assertEquals('r2d2:flatten:manageable-components', $this->command::getDefaultName());
    }
}
