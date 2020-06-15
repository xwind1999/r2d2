<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\PriceInformation\Price;
use App\Contract\Request\BroadcastListener\PriceInformation\Product;
use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Exception\Manager\Experience\OutdatedExperiencePriceException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Handler\PriceInformationBroadcastHandler;
use App\Manager\BoxManager;
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
    private $experienceManager;

    /**
     * @var BoxManager|ObjectProphecy
     */
    private $boxManager;

    private PriceInformationBroadcastHandler $priceInformationBroadcastHandler;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->boxManager = $this->prophesize(BoxManager::class);
        $this->priceInformationBroadcastHandler = new PriceInformationBroadcastHandler(
            $this->logger->reveal(),
            $this->experienceManager->reveal(),
            $this->boxManager->reveal()
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
        $this->experienceManager->insertPriceInfo($priceInformationRequest)->shouldBeCalled();
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
        $this->experienceManager->insertPriceInfo($priceInformationRequest)->shouldBeCalled()->willThrow(new \Exception());
        $this->logger->warning(Argument::any(), Argument::any())->shouldBeCalled()->willReturn(Void_::class);

        $this->expectException(\Exception::class);
        $this->assertEmpty($this->priceInformationBroadcastHandler->__invoke($priceInformationRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\BroadcastListener\PriceInformationRequest::getContext
     *
     * @dataProvider priceInformationRequestProvider
     */
    public function testHandleOutdatedMessage(PriceInformationRequest $priceInformationRequest): void
    {
        $exception = new OutdatedExperiencePriceException();
        $this->experienceManager->insertPriceInfo($priceInformationRequest)->shouldBeCalled()->willThrow($exception);
        $this->logger->warning($exception, Argument::any())->shouldBeCalled()->willReturn(Void_::class);

        $this->expectException(OutdatedExperiencePriceException::class);
        $this->priceInformationBroadcastHandler->__invoke($priceInformationRequest);
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\BroadcastListener\PriceInformationRequest::getContext
     *
     * @dataProvider priceInformationRequestProvider
     */
    public function testHandlerMessageCatchesExperienceNotFoundException(PriceInformationRequest $priceInformationRequest): void
    {
        $this->experienceManager->insertPriceInfo($priceInformationRequest)->shouldBeCalled()->willThrow(new ExperienceNotFoundException());
        $this->boxManager->insertPriceInfo($priceInformationRequest)->shouldBeCalledOnce();

        $this->assertEmpty($this->priceInformationBroadcastHandler->__invoke($priceInformationRequest));
    }

    public function priceInformationRequestProvider(): iterable
    {
        $productDTO = new Product();
        $productDTO->id = '1264';
        $priceDTO = new Price();
        $priceDTO->amount = 12;
        $priceDTO->currencyCode = 'EUR';
        $priceInformationRequest = new PriceInformationRequest();
        $priceInformationRequest->product = $productDTO;
        $priceInformationRequest->averageValue = $priceDTO;
        $priceInformationRequest->averageCommission = 5556;
        $priceInformationRequest->averageCommissionType = 'percentage';
        $priceInformationRequest->updatedAt = new \DateTime('2020-01-01 01:00:00');

        $priceInformationRequestAmount = $priceInformationRequest;
        $priceInformationRequestAmount->averageCommissionType = 'amount';

        yield [$priceInformationRequest, $priceInformationRequestAmount];
    }
}
