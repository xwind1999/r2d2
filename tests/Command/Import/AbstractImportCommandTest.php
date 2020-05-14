<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Helper\CSVParser;
use League\Csv\Reader;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractImportCommandTest extends KernelTestCase
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
    protected ObjectProphecy $validator;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->helper = $this->prophesize(CSVParser::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->application = new Application(static::createKernel());
        $this->messageBus->dispatch(Argument::any())->willReturn(new Envelope($this->requestClass));
    }

    protected function executeWithInvalidData(\Iterator $requestIterator): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($requestIterator);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
        ]);

        $output = $this->commandTester->getDisplay();

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

    public function requestProvider(): ?\Generator
    {
        $reader = Reader::createFromPath($this->filename, 'r');
        $reader->setHeaderOffset(0);
        $records = new \ArrayIterator($reader->jsonSerialize());

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $reader = Reader::createFromPath($this->invalidFilename, 'r');
        $reader->setHeaderOffset(0);
        $records = new \ArrayIterator($reader->jsonSerialize());

        yield [$records];
    }
}
