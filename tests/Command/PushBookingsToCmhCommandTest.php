<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\PushBookingsToCmhCommand;
use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Entity\Experience;
use App\Entity\Guest;
use App\Exception\Repository\BookingNotFoundException;
use App\Helper\CSVParser;
use App\Repository\BookingRepository;
use App\Tests\ProphecyKernelTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Command\PushBookingsToCmhCommand
 * @group import
 */
class PushBookingsToCmhCommandTest extends ProphecyKernelTestCase
{
    /**
     * @var ObjectProphecy | LoggerInterface
     */
    private ObjectProphecy $logger;

    /**
     * @var ObjectProphecy | MessageBusInterface
     */
    private ObjectProphecy $messageBus;

    /**
     * @var ObjectProphecy | CSVParser
     */
    private ObjectProphecy $csvParser;

    /**
     * @var ObjectProphecy | BookingRepository
     */
    private ObjectProphecy $bookingRepository;

    private PushBookingsToCmhCommand $command;

    private CommandTester $commandTester;

    public function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->csvParser = $this->prophesize(CSVParser::class);
        $this->bookingRepository = $this->prophesize(BookingRepository::class);
        $application = new Application(static::createKernel());
        $this->command = new PushBookingsToCmhCommand(
            $this->csvParser->reveal(),
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->bookingRepository->reveal()
        );
        $this->commandTester = new CommandTester($this->command);
        $application->add($this->command);
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to send bookings to CMH', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
        $this->assertEquals('BATCH SIZE', $definition->getArgument('batchSize')->getDescription());
        $this->assertEquals('r2d2:eai:push-bookings', $this->command::getDefaultName());
    }

    /**
     * @cover ::execute
     * @cover ::transformFromIterator
     * @cover ::processComponents
     * @dataProvider bookingGoldenIdListProvider
     */
    public function testExecuteSuccessfully(
        \Iterator $goldenIds,
        array $bookings,
        callable $stubs,
        callable $asserts
    ): void {
        $stubs($this, $goldenIds, $bookings);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '2',
        ]);

        $output = $this->commandTester->getDisplay();

        $asserts($this, $output);
    }

    public function bookingGoldenIdListProvider()
    {
        $goldenIds = new \ArrayIterator(
            [
                [
                    'golden_id' => 'BONBEJBO201015637968',
                ],
                [
                    'golden_id' => 'BONBEJBO201015865298',
                ],
                [
                    'golden_id' => 'BONBEJBO201016052652',
                ],
                [
                    'golden_id' => 'BONBEJBO201016151730',
                ],
                [
                    'golden_id' => 'BONBEJBO201016396014',
                ],
                [
                    'golden_id' => 'BONBEJBO201016860606',
                ],
                [
                    'golden_id' => 'BONBEJBO201022128856',
                ],
                [
                    'golden_id' => 'BONBEJBO201022151966',
                ],
                [
                    'golden_id' => 'BONBEJBO201022282041',
                ],
                [
                    'golden_id' => 'BONBEJBO201022385493',
                ],
            ]
        );

        $booking = new Booking();
        $booking->goldenId = $goldenIds[rand(0, 9)]['golden_id'];
        $booking->box = '2406';
        $booking->experience = new Experience();
        $booking->experienceGoldenId = '3216334';
        $booking->experience->components = [
            'Cup of tea',
            'Una noche muy buena',
        ];
        $booking->currency = 'EUR';
        $booking->voucher = '198257918';
        $booking->startDate = new \DateTime('2020-01-01');
        $booking->endDate = new \DateTime('2020-01-02');
        $booking->customerComment = 'Clean sheets please';
        $booking->createdAt = new \DateTime('yesterday');
        $booking->updatedAt = new \DateTime('now');
        $booking->partnerGoldenId = '13456';
        $booking->totalPrice = 500;
        $booking->country = 'FR';
        $booking->guests = [new Guest()];
        $booking->guests[0]->firstName = 'Hermano';
        $booking->guests[0]->lastName = 'Guido';
        $booking->guests[0]->email = 'maradona@worldcup.ar';
        $booking->guests[0]->phone = '123 123 123';
        $booking->guests[0]->isPrimary = true;
        $booking->guests[0]->age = 30;
        $booking->guests[0]->country = 'AR';

        yield 'push-completed-bookings' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

                return [$booking];
            })(clone $booking),
            (function ($test, \Iterator $goldenIds, array $bookings) {
                $test->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
                $test->bookingRepository->findListByGoldenId(Argument::any())->shouldBeCalledTimes(6)->willReturn($bookings);
                $test->logger->error(Argument::any())->shouldNotBeCalled();
                $test->messageBus
                    ->dispatch(Argument::any())
                    ->shouldBeCalledTimes(6)
                    ->willReturn(new Envelope(new \stdClass()));
            }),
            (function ($test, string $output) {
                $test->assertEquals(0, $test->commandTester->getStatusCode());
                $test->assertStringContainsString('Total CSV IDs received: 10', $output);
                $test->assertStringContainsString('Total Collection IDs processed with success: 6', $output);
                $test->assertStringContainsString('Command executed', $output);
                $test->assertStringContainsString('Starting at: ', $output);
                $test->assertStringContainsString('Finishing at : ', $output);
            }),
        ];

        yield 'push-canceled-bookings' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CANCELLED;

                return [$booking];
            })(clone $booking),
            (function ($test, \Iterator $goldenIds, array $bookings) {
                $test->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
                $test->bookingRepository->findListByGoldenId(Argument::any())->shouldBeCalledTimes(6)->willReturn($bookings);
                $test->logger->error(Argument::any())->shouldNotBeCalled();
                $test->messageBus
                    ->dispatch(Argument::any())
                    ->shouldBeCalledTimes(6)
                    ->willReturn(new Envelope(new \stdClass()));
            }),
            (function ($test, string $output) {
                $test->assertEquals(0, $test->commandTester->getStatusCode());
                $test->assertStringContainsString('Total CSV IDs received: 10', $output);
                $test->assertStringContainsString('Total Collection IDs processed with success: 6', $output);
                $test->assertStringContainsString('Command executed', $output);
                $test->assertStringContainsString('Starting at: ', $output);
                $test->assertStringContainsString('Finishing at : ', $output);
            }),
        ];

        yield 'push-created-bookings' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CREATED;

                return [$booking];
            })(clone $booking),
            (function ($test, \Iterator $goldenIds) {
                $test->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
                $test->bookingRepository->findListByGoldenId(Argument::any())->shouldBeCalledTimes(6);
                $test->logger->error(Argument::any())->shouldNotBeCalled();
                $test->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
            }),
            (function ($test, string $output) {
                $test->assertEquals(0, $test->commandTester->getStatusCode());
                $test->assertStringContainsString('Total CSV IDs received: 10', $output);
                $test->assertStringContainsString('Total Collection IDs processed with success: 0', $output);
                $test->assertStringContainsString('Command executed', $output);
                $test->assertStringContainsString('Starting at: ', $output);
                $test->assertStringContainsString('Finishing at : ', $output);
            }),
        ];

        yield 'booking-not-found-throws-exception' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

                return [$booking];
            })(clone $booking),
            (function ($test, \Iterator $goldenIds) {
                $test->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
                $test->bookingRepository->findListByGoldenId(Argument::any())->shouldBeCalled()
                    ->willThrow(BookingNotFoundException::class);
                $test->logger->error(Argument::any())->shouldBeCalled();
                $test->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
            }),
            (function ($test) {
                $test->assertEquals(0, $test->commandTester->getStatusCode());
                $test->assertStringContainsString(
                    'Failed items',
                    $test->commandTester->getDisplay()
                );
            }),
        ];

        yield 'process-throws-exception' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;

                return [$booking];
            })(clone $booking),
            (function ($test, \Iterator $goldenIds, array $bookings) {
                $test->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
                $test->bookingRepository->findListByGoldenId(Argument::any())->shouldBeCalled()
                    ->willReturn($bookings);
                $test->logger->error(Argument::any())->shouldBeCalled();
                $test->messageBus->dispatch(Argument::any())->shouldBeCalled()->willThrow(\Exception::class);
            }),
            (function ($test) {
                $test->assertEquals(1, $test->commandTester->getStatusCode());
            }),
        ];
    }
}
