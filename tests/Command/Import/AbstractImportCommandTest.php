<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Helper\CSVParser;
use App\Tests\ProphecyKernelTestCase;
use JMS\Serializer\SerializerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractImportCommandTest extends ProphecyKernelTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected ObjectProphecy $logger;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    protected ObjectProphecy $messageBus;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    protected ObjectProphecy $productRequest;

    /**
     * @var CSVParser|ObjectProphecy
     */
    protected ObjectProphecy $helper;

    protected Application $application;
    protected Command $command;
    protected CommandTester $commandTester;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    protected ObjectProphecy $validator;
    /**
     * @var ObjectProphecy|SerializerInterface
     */
    protected $serializer;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->helper = $this->prophesize(CSVParser::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->application = new Application(static::createKernel());
        $this->messageBus->dispatch(Argument::any())->willReturn(new Envelope($this->requestClass));
        $this->serializer = $this->prophesize(SerializerInterface::class);
    }

    protected function executeWithInvalidData(\Iterator $requestIterator): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($requestIterator);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->logger->error(Argument::any())->shouldHaveBeenCalled();
        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertIsString($output, 'The output must be a stream');
        $this->assertStringContainsString('Command exited', $this->commandTester->getDisplay());
    }

    protected function executeWithValidData(\Iterator $requestIterator): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($requestIterator);
        $errors = new ConstraintViolationList([]);
        $this->validator->validate(Argument::any())->willReturn($errors);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertIsString($output, 'The output must be a stream');
        $this->assertStringContainsString('Command executed', $this->commandTester->getDisplay());
    }

    abstract public function requestProvider(): ?\Generator;

    abstract public function requestProviderInvalidData(): ?\Generator;
}
