<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\PartnerImportCommand;
use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Helper\CSVParser;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \App\Command\Import\PartnerImportCommand
 */
class PartnerImportCommandTest extends KernelTestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    private $validator;

    /**
     * @var CSVParser|ObjectProphecy
     */
    private $helper;

    /**
     * @var ObjectProphecy|PartnerRequest
     */
    private $partnerRequest;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->helper = $this->prophesize(CSVParser::class);
        $this->partnerRequest = $this->prophesize(PartnerRequest::class);
    }

    public function partnerRequestProvider(): iterable
    {
        $iterator = new \ArrayIterator([
            0 => [
                'Account_URN__c' => '00016503',
                'Type' => 'partner',
                'CurrencyIsoCode' => 'EUR',
                'CeaseDate__c' => null,
                'Channel_Manager_Active__c' => false,
            ],
            1 => [
                'Account_URN__c' => '00016504',
                'Type' => 'partner',
                'CurrencyIsoCode' => 'EUREXTRA',
                'CeaseDate__c' => null,
                'Channel_Manager_Active__c' => false,
            ],
            2 => [
                'Account_URN__c' => '00016505',
                'Type' => 'partnerandveryveryveryverylong',
                'CurrencyIsoCode' => 'EUR',
                'CeaseDate__c' => null,
                'Channel_Manager_Active__c' => false,
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
     * @dataProvider partnerRequestProvider
     */
    public function testExecuteSuccessfully(\ArrayIterator $requestArray): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($requestArray);
        $this->validator->validate(Argument::any())->willReturn(new ConstraintViolationList([]));
        $this->messageBus->dispatch(Argument::any())->willReturn(new Envelope($this->partnerRequest->reveal()));

        $application = new Application(static::createKernel());

        $command = new PartnerImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => 'Partners_tests.csv',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertEquals('r2d2:partner:import', $command::getDefaultName());
        $this->assertStringContainsString('[OK] Command executed', $commandTester->getDisplay());
        $this->assertStringContainsString('Total records: 3', $commandTester->getDisplay());
        $this->assertStringContainsString('Starting at:', $commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at :', $commandTester->getDisplay());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers ::logError
     *
     * @dataProvider partnerRequestProvider
     */
    public function testExecuteWithInvalidData(\ArrayIterator $requestArray): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($requestArray);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $application = new Application(static::createKernel());
        $command = new PartnerImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'file' => 'Partners_tests.csv']);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers ::logError
     *
     * @dataProvider partnerRequestProvider
     */
    public function testExecuteCatchesException(\ArrayIterator $requestArray): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($requestArray);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));

        $application = new Application(static::createKernel());
        $command = new PartnerImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'file' => 'Partners_tests.csv']);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('Total records: 3', $commandTester->getDisplay());
        $this->assertStringContainsString('Starting at:', $commandTester->getDisplay());
        $this->assertStringContainsString('[ERROR] Command exited', $commandTester->getDisplay());
    }
}
