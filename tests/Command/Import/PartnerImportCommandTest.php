<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\Import\PartnerImportCommand;
use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Tests\Command\Import\AbstractImportCommandTest;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \App\Command\Import\PartnerImportCommand
 * @group partner-import-command
 */
class PartnerImportCommandTest extends AbstractImportCommandTest
{
    protected ObjectProphecy $partnerRequest;

    protected ObjectProphecy $requestClass;

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(PartnerRequest::class);
        parent::setUp();

        $this->command = new PartnerImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal());

        $this->commandTester = new CommandTester($this->command);
        $this->application->add($this->command);
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @dataProvider requestProvider
     */
    public function testExecuteWithInvalidData(\Iterator $partnerRequests): void
    {
        $this->executeWithInvalidData($partnerRequests);

        $this->assertEquals('r2d2:partner:import', $this->command::getDefaultName());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @dataProvider requestProvider
     */
    public function testExecuteWithValidData(\Iterator $partnerRequests): void
    {
        $this->executeWithValidData($partnerRequests);

        $this->assertEquals('r2d2:partner:import', $this->command::getDefaultName());
    }

    public function requestProvider(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'Account_URN__c' => '16503',
                'Type' => 'partner',
                'CurrencyIsoCode' => 'EUR',
                'CeaseDate__c' => '2015-06-04',
                'Channel_Manager_Active__c' => '0',
            ],
        ]);

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'Account_URN__c' => '9999999999999999999999',
                'Type' => 'partner',
                'CurrencyIsoCode' => '',
                'CeaseDate__c' => new \DateTime('now'),
                'Channel_Manager_Active__c' => '',
            ],
        ]);

        yield [$records];
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to push CSV partner to the queue', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
