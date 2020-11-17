<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\RoomPriceImportCommand;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Helper\CSVParser;
use App\Helper\MoneyHelper;
use App\Tests\ProphecyKernelTestCase;
use JMS\Serializer\SerializerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \App\Command\Import\RoomPriceImportCommand
 */
class RoomPriceImportCommandTest extends ProphecyKernelTestCase
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

    protected RoomPriceImportCommand $command;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    protected ObjectProphecy $validator;
    /**
     * @var ObjectProphecy|SerializerInterface
     */
    protected $serializer;

    /**
     * @var MoneyHelper|ObjectProphecy
     */
    protected $moneyHelper;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->helper = $this->prophesize(CSVParser::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->messageBus->dispatch(Argument::any())->willReturn(new Envelope(new \stdClass()));
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->moneyHelper = $this->prophesize(MoneyHelper::class);

        $this->command = new RoomPriceImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal(),
            $this->serializer->reveal(),
            $this->moneyHelper->reveal()
        );
    }

    public function testProcess(): void
    {
        $item = [
            'product.id' => '1234567',
            'dateFrom' => '2020-01-01',
            'dateTo' => '2020-01-02',
            'price.amount' => 30.00,
            'price.currencyCode' => 'EUR',
            'updatedAt' => '2020-01-01 00:00:00',
        ];

        $iterator = new \ArrayIterator([$item]);

        $constraintViolation = $this->prophesize(ConstraintViolationListInterface::class);
        $constraintViolation->count()->willReturn(0);
        $this->validator->validate(Argument::type(RoomPriceRequest::class))->shouldBeCalled()->willReturn($constraintViolation->reveal());

        $amount = 3000;
        $this->moneyHelper->convertToInteger('30.00', 'EUR')->willReturn($amount);

        $that = $this;
        $this
            ->messageBus
            ->dispatch(Argument::type(RoomPriceRequest::class))
            ->will(function ($args) use ($that, $item, $amount) {
                /** @var RoomPriceRequest $roomPriceRequest */
                $roomPriceRequest = $args[0];
                $that->assertEquals($roomPriceRequest->product->id, $item['product.id']);
                $that->assertEquals($roomPriceRequest->dateFrom, new \DateTime($item['dateFrom']));
                $that->assertEquals($roomPriceRequest->dateTo, new \DateTime($item['dateTo']));
                $that->assertEquals($roomPriceRequest->price->amount, $amount);
                $that->assertEquals($roomPriceRequest->price->currencyCode, $item['price.currencyCode']);
                $that->assertEquals($roomPriceRequest->updatedAt, new \DateTime($item['updatedAt']));

                return new Envelope(new \stdClass());
            })
            ->shouldBeCalled();

        $this->command->process($iterator);
    }

    public function testProcessWithInvalidData(): void
    {
        $item = [
            'product.id' => '1234567',
            'dateFrom' => '2020-01-01',
            'dateTo' => '2020-01-02',
            'price.amount' => 30.00,
            'price.currencyCode' => 'EUR',
            'updatedAt' => '2020-01-01 00:00:00',
        ];

        $iterator = new \ArrayIterator([$item]);
        $v = new ConstraintViolation('aaa', 'aaa', [], '', '', '', null, '', null, '');
        $constraintViolation = new ConstraintViolationList([$v]);
        $this->validator->validate(Argument::type(RoomPriceRequest::class))->shouldBeCalled()->willReturn($constraintViolation);

        $amount = 3000;
        $this->moneyHelper->convertToInteger('30.00', 'EUR')->willReturn($amount);

        $this
            ->messageBus
            ->dispatch(Argument::type(RoomPriceRequest::class))
            ->shouldNotBeCalled();

        $this->expectException(\InvalidArgumentException::class);
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalled();

        $this->command->process($iterator);
    }
}
