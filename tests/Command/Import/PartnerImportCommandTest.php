<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\PartnerImportCommand;
use App\Contract\Request\BroadcastListener\PartnerRequest;
use Prophecy\Argument;
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
        $this->messageBus
            ->dispatch(Argument::type(PartnerRequest::class))
            ->shouldBeCalledTimes(count($partnerRequests));
    }

    public function requestProvider(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '16503',
                'type' => 'partner',
                'currencyCode' => 'EUR',
                'partnerCeaseDate' => '2015-10-12T23:03:09.000000+0000',
                'isChannelManagerEnabled' => '0',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$records];

        $records = new \ArrayIterator([
            [
                'id' => '16503',
                'type' => 'partner',
                'currencyCode' => 'EUR',
                'partnerCeaseDate' => '2015-10-12T23:03:09.000000+0000',
                'isChannelManagerEnabled' => '0',
            ],
        ]);

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '9999999999999999999999',
                'type' => 'partner',
                'partnerCeaseDate' => new \DateTime('now'),
                'isChannelManagerEnabled' => '',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
            [
                'id' => '9999999999999999999999',
                'type' => 'partner',
                'partnerCeaseDate' => null,
                'isChannelManagerEnabled' => '',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
            [
                'id' => '9999999999999999999999',
                'type' => 'partner',
                'partnerCeaseDate' => null,
                'isChannelManagerEnabled' => '',
                'updatedAt' => null,
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
