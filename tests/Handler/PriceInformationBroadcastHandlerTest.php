<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformation\Product;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Handler\PriceInformationBroadcastHandler;
use App\Manager\ExperienceManager;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\PriceInformationBroadcastHandler
 */
class PriceInformationBroadcastHandlerTest extends TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var ExperienceManager|ObjectProphecy
     */
    private $manager;

    private PriceInformationBroadcastHandler $priceInformationBroadcastHandler;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->manager = $this->prophesize(ExperienceManager::class);
        $this->priceInformationBroadcastHandler = new PriceInformationBroadcastHandler(
            $this->logger->reveal(),
            $this->manager->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     *
     * @dataProvider priceInformationRequestProvider
     */
    public function testHandlerMessage(PriceInformationRequest $priceInformationRequest): void
    {
        $this->manager->insertPriceInfo($priceInformationRequest)->shouldBeCalled();
        $this->assertEmpty($this->priceInformationBroadcastHandler->__invoke($priceInformationRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\BroadcastListener\PriceInformationRequest::getContext
     *
     * @dataProvider priceInformationRequestProvider
     */
    public function testHandlerMessageCatchesException(PriceInformationRequest $priceInformationRequest): void
    {
        $this->manager->insertPriceInfo($priceInformationRequest)->shouldBeCalled()->willThrow(new ExperienceNotFoundException());
        $this->logger->warning(Argument::any(), Argument::any())->shouldBeCalled()->willReturn(Void_::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(1000015);
        $this->expectExceptionMessage('Experience not found');
        $this->assertEmpty($this->priceInformationBroadcastHandler->__invoke($priceInformationRequest));
    }

    public function priceInformationRequestProvider(): iterable
    {
        $productDTO = new Product();
        $productDTO->id = '1264';
        $priceDTO = new Price();
        $priceDTO->amount = 12;
        $priceInformationRequest = new PriceInformationRequest();
        $priceInformationRequest->product = $productDTO;
        $priceInformationRequest->averageValue = $priceDTO;
        $priceInformationRequest->averageCommission = 5556;
        $priceInformationRequest->averageCommissionType = 'percentage';

        $priceInformationRequestAmount = $priceInformationRequest;
        $priceInformationRequestAmount->averageCommissionType = 'amount';

        yield [$priceInformationRequest, $priceInformationRequestAmount];
    }
}
