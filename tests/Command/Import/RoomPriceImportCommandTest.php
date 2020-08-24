<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\RoomPriceImportCommand;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \App\Command\Import\RoomPriceImportCommand
 */
class RoomPriceImportCommandTest extends AbstractImportCommandTest
{
    /**
     * @var ObjectProphecy|RoomPriceRequest
     */
    protected ObjectProphecy $requestClass;

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(RoomPriceRequest::class);
        parent::setUp();
        $application = new Application(static::createKernel());

        $this->command = new RoomPriceImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal()
        );
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function requestProvider(): \Generator
    {
        $iterator = new \ArrayIterator([
            [
                'product.id' => '1234567',
                'dateFrom' => '2020-01-01',
                'dateTo' => '2020-01-02',
                'price.amount' => '30.00',
                'price.currencyCode' => 'EUR',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
        ]);

        yield [$iterator];
    }

    public function requestProviderInvalidData(): \Generator
    {
        $iterator = new \ArrayIterator([
            [
                'product.id' => '1234567',
                'dateFrom' => '2020-01-01',
                'dateTo' => '2020-01-02',
                'price.amount' => '30.00',
                'price.currencyCode' => 'EUR',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
            [
                'product.id' => '1234567',
                'dateFrom' => null,
                'dateTo' => '2020-01-02',
                'price.amount' => '30.00',
                'price.currencyCode' => 'EUR',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
            [
                'product.id' => '1234567',
                'dateFrom' => null,
                'dateTo' => '2020-01-02',
                'price.amount' => '3000',
                'price.currencyCode' => 'EUR',
                'updatedAt' => '2020-01-01 00:00:00',
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
    public function testExecuteSuccessfully(\ArrayIterator $arrayRoomPriceRequest): void
    {
        $c = count($arrayRoomPriceRequest);

        for ($i = 0; $i < $c; ++$i) {
            $roomPriceRequest = $arrayRoomPriceRequest[$i];
            (function ($tester, $roomPriceRequest) {
                $this->messageBus
                    ->dispatch(Argument::type(RoomPriceRequest::class))
                    ->will(function ($test) use ($tester, $roomPriceRequest) {
                        /** @var RoomPriceRequest $roomPrice */
                        $roomPrice = $test[0];
                        $tester->assertEquals($roomPriceRequest['product.id'], $roomPrice->product->id);
                        $tester->assertEquals($roomPriceRequest['dateFrom'], $roomPrice->dateFrom->format('Y-m-d'));
                        $tester->assertEquals($roomPriceRequest['dateTo'], $roomPrice->dateTo->format('Y-m-d'));
                        $tester->assertEquals(new \DateTime($roomPriceRequest['updatedAt']), $roomPrice->updatedAt);
                        $tester->assertEquals($roomPriceRequest['price.amount'] * 100, $roomPrice->price->amount);
                        $tester->assertEquals($roomPriceRequest['price.currencyCode'], $roomPrice->price->currencyCode);

                        return new Envelope(new \stdClass());
                    })
                    ->shouldBeCalledTimes(1);
            })($this, $roomPriceRequest);
        }

        $this->executeWithValidData($arrayRoomPriceRequest);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('r2d2:room-price:import', $this->command::getDefaultName());
        $this->assertStringContainsString('[OK] Command executed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Total records: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at:', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at :', $this->commandTester->getDisplay());
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
    public function testExecuteWithInvalidData(\ArrayIterator $arrayRoomPriceRequest): void
    {
        $this->executeWithInvalidData($arrayRoomPriceRequest);

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
    public function testExecuteCatchesException(\ArrayIterator $arrayRoomPriceRequest): void
    {
        $this->helper->readFile(Argument::any(), Argument::any())->willReturn($arrayRoomPriceRequest);
        $errors = new ConstraintViolationList([]);
        $errors->add(new ConstraintViolation(Argument::any(), null, [], Argument::any(), null, null));
        $this->validator->validate(Argument::any())->willReturn($errors);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'RoomPrices_Test.csv',
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

        $this->assertEquals('Command to push CSV room-price to the queue', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
