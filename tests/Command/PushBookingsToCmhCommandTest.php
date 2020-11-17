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
        int $dipatchCount
    ): void {
        $this->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
        $this->bookingRepository->findListByGoldenId(Argument::any())->shouldBeCalledTimes(6)->willReturn($bookings);
        $this->logger->error(Argument::any())->shouldNotBeCalled();
        $this->messageBus
            ->dispatch(Argument::any())
            ->shouldBeCalledTimes($dipatchCount)
            ->willReturn(new Envelope(new \stdClass()));

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '2',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Total CSV IDs received: 10', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Total Collection IDs read: 6', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Command executed', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Starting at: ', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Finishing at : ', $this->commandTester->getDisplay());
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
            6,
        ];

        yield 'push-canceled-bookings' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CANCELLED;

                return [$booking];
            })(clone $booking),
            6,
        ];

        yield 'push-created-bookings' => [
            $goldenIds,
            (function ($booking) {
                $booking->status = BookingStatusConstraint::BOOKING_STATUS_CREATED;

                return [$booking];
            })(clone $booking),
            0,
        ];
    }

    /**
     * @cover ::execute
     * @cover ::process
     * @dataProvider bookingGoldenIdListProvider
     */
    public function testExecuteThrowsBookingNotFoundException(\Iterator $goldenIds): void
    {
        $this->csvParser->readFile(Argument::any(), Argument::any())->willReturn($goldenIds);
        $this->bookingRepository
            ->findListByGoldenId(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(BookingNotFoundException::class)
        ;
        $this->logger->error(Argument::any())->shouldBeCalledOnce();
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->logger->error(Argument::any())->shouldBeCalledOnce();
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'file' => 'Import_Command.csv',
            'batchSize' => '1',
        ]);
        $this->commandTester->getDisplay();
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }
}
