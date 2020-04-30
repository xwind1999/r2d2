<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\RelationshipImportCommand;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
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
 * @coversDefaultClass \App\Command\Import\RelationshipImportCommand
 */
class RelationshipImportCommandTest extends KernelTestCase
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
     * @var ObjectProphecy|ProductRelationshipRequest
     */
    private $productRelationshipRequest;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->helper = $this->prophesize(CSVParser::class);
        $this->productRelationshipRequest = $this->prophesize(ProductRelationshipRequest::class);
    }

    public function productRelationshipRequestProvider(): iterable
    {
        $iterator = new \ArrayIterator([
            0 => [
                'parentProduct' => 'BB0000335658',
                'childProduct' => 'HG0000335654',
                'sortOrder' => 1,
                'isEnabled' => true,
                'relationshipType' => 'Box-Experience',
                'printType' => 'Digital',
                'childCount' => 4,
                'childQuantity' => 0,
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
     * @dataProvider productRelationshipRequestProvider
     */
    public function testExecuteSuccessfully(\ArrayIterator $arrayProductRelationshipRequest): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($arrayProductRelationshipRequest);
        $this->validator->validate(Argument::any())->willReturn(new ConstraintViolationList([]));
        $this->messageBus->dispatch(Argument::any())->willReturn(new Envelope($this->productRelationshipRequest->reveal()));

        $application = new Application(static::createKernel());

        $command = new RelationshipImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => 'Relationships_tests.csv',
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertEquals('r2d2:relationship:import', $command::getDefaultName());
        $this->assertStringContainsString('[OK] Command executed', $commandTester->getDisplay());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers ::logError
     *
     * @dataProvider productRelationshipRequestProvider
     */
    public function testExecuteWithInvalidData(\ArrayIterator $arrayProductRelationshipRequest): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($arrayProductRelationshipRequest);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $application = new Application(static::createKernel());
        $command = new RelationshipImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'file' => 'Relationships_tests.csv']);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers ::logError
     *
     * @dataProvider productRelationshipRequestProvider
     */
    public function testExecuteCatchesException(\ArrayIterator $arrayProductRelationshipRequest): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($arrayProductRelationshipRequest);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));

        $application = new Application(static::createKernel());
        $command = new RelationshipImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'file' => 'Relationships_tests.csv']);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('[ERROR] Command exited', $commandTester->getDisplay());
    }
}
