<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\RelationshipImportCommand;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \App\Command\Import\RelationshipImportCommand
 * @group relationship-import-command
 */
class RelationshipImportCommandTest extends AbstractImportCommandTest
{
    /**
     * @var ObjectProphecy|ProductRelationshipRequest
     */
    protected ObjectProphecy $requestClass;

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(ProductRelationshipRequest::class);
        parent::setUp();
        $application = new Application(static::createKernel());

        $this->command = new RelationshipImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal(),
            $this->serializer->reveal()
        );
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function requestProvider(): \Generator
    {
        $iterator = new \ArrayIterator([
            [
                'parentProduct' => 'BB0000335658',
                'childProduct' => 'HG0000335654',
                'isEnabled' => true,
                'relationshipType' => 'Box-Experience',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$iterator];

        $iterator = new \ArrayIterator([
            [
                'parentProduct' => 'BB0000335658',
                'childProduct' => 'HG0000335654',
                'isEnabled' => true,
                'relationshipType' => 'Box-Experience',
            ],
        ]);

        yield [$iterator];
    }

    public function requestProviderInvalidData(): \Generator
    {
        $iterator = new \ArrayIterator([
            [
                'parentProduct' => 'BB0000335658',
                'childProduct' => 'HG0000335654',
                'isEnabled' => 'abc',
                'relationshipType' => 'Box-Experience',
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
    public function testExecuteSuccessfully(\ArrayIterator $arrayProductRelationshipRequest): void
    {
        $this->executeWithValidData($arrayProductRelationshipRequest);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('r2d2:relationship:import', $this->command::getDefaultName());
        $this->assertStringContainsString('[OK] Command executed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Total records: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at:', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at :', $this->commandTester->getDisplay());
        $this->messageBus
            ->dispatch(Argument::type(ProductRelationshipRequest::class))
            ->shouldBeCalledTimes(count($arrayProductRelationshipRequest));
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
    public function testExecuteWithInvalidData(\ArrayIterator $arrayProductRelationshipRequest): void
    {
        $this->executeWithInvalidData($arrayProductRelationshipRequest);

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
    public function testExecuteCatchesException(\ArrayIterator $arrayProductRelationshipRequest): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($arrayProductRelationshipRequest);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Relationship_Test.csv',
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

        $this->assertEquals('Command to push CSV relationship to the queue', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
