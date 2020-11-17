<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\ProductBoxImportCommand;
use App\Contract\Request\BroadcastListener\ProductRequest;
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
class ProductBoxImportCommandTest extends ProphecyKernelTestCase
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

    protected ProductBoxImportCommand $command;

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

        $this->command = new ProductBoxImportCommand(
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
            'id' => '1304',
            'sellableBrand' => 'SBX',
            'sellableCountry' => 'FR',
            'status' => 'ready',
            'listPrice.amount' => 30.00,
            'listPrice.currencyCode' => 'EUR',
            'universe.id' => 'STA',
            'type' => 'mev',
            'updatedAt' => '2020-01-01 00:00:00',
        ];

        $iterator = new \ArrayIterator([$item]);

        $constraintViolation = $this->prophesize(ConstraintViolationListInterface::class);
        $constraintViolation->count()->willReturn(0);
        $this->validator->validate(Argument::type(ProductRequest::class))->shouldBeCalled()->willReturn($constraintViolation->reveal());

        $amount = 3000;
        $this->moneyHelper->convertToInteger('30.00', 'EUR')->willReturn($amount);

        $that = $this;
        $this
            ->messageBus
            ->dispatch(Argument::type(ProductRequest::class))
            ->will(function ($args) use ($that, $item, $amount) {
                /** @var ProductRequest $productRequest */
                $productRequest = $args[0];
                $that->assertEquals($productRequest->id, $item['id']);
                $that->assertEquals($productRequest->sellableBrand->code, $item['sellableBrand']);
                $that->assertEquals($productRequest->sellableCountry->code, $item['sellableCountry']);
                $that->assertEquals($productRequest->status, $item['status']);
                $that->assertEquals($productRequest->listPrice->amount, $amount);
                $that->assertEquals($productRequest->listPrice->currencyCode, $item['listPrice.currencyCode']);
                $that->assertEquals($productRequest->universe->id, $item['universe.id']);
                $that->assertEquals($productRequest->type, $item['type']);
                $that->assertEquals($productRequest->updatedAt, new \DateTime($item['updatedAt']));

                return new Envelope(new \stdClass());
            })
            ->shouldBeCalled()
        ;

        $this->command->process($iterator);
    }

    public function testProcessWithInvalidData(): void
    {
        $item = [
            'id' => '1304',
            'sellableBrand' => 'AAAAAAAAA',
            'sellableCountry' => 'FR',
            'status' => 'ready',
            'listPrice.amount' => 30.00,
            'listPrice.currencyCode' => 'EUR',
            'universe.id' => 'STA',
            'type' => 'mev',
            'updatedAt' => '2020-01-01 00:00:00',
        ];

        $iterator = new \ArrayIterator([$item]);
        $v = new ConstraintViolation('aaa', 'aaa', [], '', '', '', null, '', null, '');
        $constraintViolation = new ConstraintViolationList([$v]);
        $this->validator->validate(Argument::type(ProductRequest::class))->shouldBeCalled()->willReturn($constraintViolation);

        $amount = 3000;
        $this->moneyHelper->convertToInteger('30.00', 'EUR')->willReturn($amount);

        $this
            ->messageBus
            ->dispatch(Argument::type(ProductRequest::class))
            ->shouldNotBeCalled();

        $this->expectException(\InvalidArgumentException::class);
        $this->logger->error(Argument::any(), Argument::any())->shouldBeCalled();

        $this->command->process($iterator);
    }
}
