<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\PriceInformationImportCommand;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
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
 * @coversDefaultClass \App\Command\Import\PriceInformationImportCommand
 */
class PriceInformationImportCommandTest extends ProphecyKernelTestCase
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

    protected PriceInformationImportCommand $command;

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

        $this->command = new PriceInformationImportCommand(
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
            'product.id' => 'BB0000335658',
            'averageValue.amount' => 30.99,
            'averageValue.currencyCode' => 'SEK',
            'averageCommissionType' => 'amount',
            'averageCommission' => '20.00',
            'updatedAt' => '2020-01-01 00:00:00',
        ];

        $iterator = new \ArrayIterator([$item]);

        $amount = 3099;
        $this->moneyHelper->convertToInteger('30.99', 'SEK')->willReturn($amount);

        $constraintViolation = $this->prophesize(ConstraintViolationListInterface::class);
        $constraintViolation->count()->willReturn(0);
        $this->validator->validate(Argument::type(PriceInformationRequest::class))->shouldBeCalled()->willReturn($constraintViolation->reveal());

        $that = $this;
        $this
            ->messageBus
            ->dispatch(Argument::type(PriceInformationRequest::class))
            ->will(function ($args) use ($item, $that, $amount) {
                $that->assertEquals($args[0]->averageValue->currencyCode, $item['averageValue.currencyCode']);
                $that->assertEquals($args[0]->averageValue->amount, $amount);
                $that->assertEquals($args[0]->product->id, $item['product.id']);
                $that->assertEquals($args[0]->averageCommissionType, $item['averageCommissionType']);
                $that->assertEquals($args[0]->averageCommission, $item['averageCommission']);
                $that->assertEquals($args[0]->updatedAt, new \DateTime($item['updatedAt']));

                return new Envelope(new \stdClass());
            });

        $this->command->process($iterator);
    }

    public function testProcessWithInvalidData(): void
    {
        $item = [
            'product.id' => 'BB0000335658',
            'averageValue.amount' => 30.99,
            'averageValue.currencyCode' => 'EUR',
            'averageCommissionType' => 'amount',
            'averageCommission' => '20.00',
            'updatedAt' => '2020-01-01 00:00:00',
        ];

        $amount = 3099;
        $this->moneyHelper->convertToInteger('30.99', 'EUR')->willReturn($amount);

        $iterator = new \ArrayIterator([$item]);
        $v = new ConstraintViolation('aaa', 'aaa', [], '', '', '', null, '', null, '');
        $constraintViolation = new ConstraintViolationList([$v]);
        $this->validator->validate(Argument::type(PriceInformationRequest::class))->shouldBeCalled()->willReturn($constraintViolation);

        $this
            ->messageBus
            ->dispatch(Argument::type(PriceInformationRequest::class))
            ->shouldNotBeCalled();

        $this->expectException(\InvalidArgumentException::class);
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalled();

        $this->command->process($iterator);
    }
}
