<?php

declare(strict_types=1);

namespace App\Tests\Resolver;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageablePartnerEvent;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;
use App\Resolver\ManageableProductResolver;
use App\Tests\ProphecyTestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Resolver\ManageableProductResolver
 */
class ManageableProductResolverTest extends ProphecyTestCase
{
    /**
     * @var ManageableProductRequest|ObjectProphecy
     */
    private $manageableProductRequest;

    /**
     * @var ObjectProphecy|ProductRequest
     */
    private $productRequest;

    /**
     * @var ObjectProphecy|ProductRelationshipRequest
     */
    private $productRelationshipRequest;

    protected function setUp(): void
    {
        $this->manageableProductRequest = $this->prophesize(ManageableProductRequest::class);
        $this->productRequest = $this->prophesize(ProductRequest::class);
        $this->productRelationshipRequest = $this->prophesize(ProductRelationshipRequest::class);
    }

    /**
     * @covers ::resolve
     * @dataProvider boxTypeProvider
     */
    public function testResolveUsingBoxSuccessfully(string $boxType): void
    {
        $this->productRequest->type = $boxType;
        $this->productRequest->id = '12345';
        $this->manageableProductRequest->setProductRequest($this->productRequest->reveal());
        $this->manageableProductRequest
            ->getProductRequest()
            ->shouldBeCalled()
            ->willReturn($this->productRequest->reveal())
        ;
        $this->manageableProductRequest->getProductRelationshipRequest()->shouldNotBeCalled();
        $manageableProductResolver = new ManageableProductResolver();
        $manageableProductResolver->resolve($this->manageableProductRequest->reveal());
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingComponentSuccessfully(): void
    {
        $this->productRequest->type = 'COMPONENT';
        $this->productRequest->id = '12345';
        $this->manageableProductRequest->setProductRequest($this->productRequest->reveal());
        $this->manageableProductRequest
            ->getProductRequest()
            ->shouldBeCalled()
            ->willReturn($this->productRequest->reveal())
        ;
        $this->manageableProductRequest->getProductRelationshipRequest()->shouldNotBeCalled();
        $manageableProductResolver = new ManageableProductResolver();
        $manageableProductResolver->resolve($this->manageableProductRequest->reveal());
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingExperienceSuccessfully(): void
    {
        $this->productRequest->type = 'experience';
        $this->productRequest->id = '12345';
        $this->manageableProductRequest->setProductRequest($this->productRequest->reveal());
        $this->manageableProductRequest
            ->getProductRequest()
            ->shouldBeCalled()
            ->willReturn($this->productRequest->reveal())
        ;
        $this->manageableProductRequest->getProductRelationshipRequest()->shouldNotBeCalled();
        $manageableProductResolver = new ManageableProductResolver();
        $manageableProductResolver->resolve($this->manageableProductRequest->reveal());
    }

    /**
     * @covers ::resolve
     * @dataProvider productRelationshipProvider
     */
    public function testResolveUsingProductRelationshipSuccessfully(string $relationshipType): void
    {
        $this->productRelationshipRequest->relationshipType = $relationshipType;
        $this->productRelationshipRequest->parentProduct = '12345';
        $this->productRelationshipRequest->childProduct = '54321';
        $this->manageableProductRequest->setProductRelationshipRequest($this->productRelationshipRequest->reveal());
        $this->manageableProductRequest->getProductRequest()->shouldBeCalled()->willReturn(null);
        $this->manageableProductRequest
            ->getProductRelationshipRequest()
            ->shouldBeCalled()
            ->willReturn($this->productRelationshipRequest->reveal())
        ;
        $manageableProductResolver = new ManageableProductResolver();
        $manageableProductResolver->resolve($this->manageableProductRequest->reveal());
    }

    /**
     * @covers ::resolve
     * @covers \App\Event\Manageable\ManageablePartnerEvent::fromPartner
     */
    public function testResolveUsingPartnerSuccessfully(): void
    {
        $partnerRequest = new PartnerRequest();
        $partnerRequest->id = '123456';
        $this->manageableProductRequest->getProductRelationshipRequest()->willReturn(null);
        $this->manageableProductRequest->getProductRequest()->willReturn(null);
        $this->manageableProductRequest
            ->getPartnerRequest()
            ->shouldBeCalled()
            ->willReturn($partnerRequest)
        ;
        $manageableProductResolver = new ManageableProductResolver();
        $resolved = $manageableProductResolver->resolve($this->manageableProductRequest->reveal());
        $this->assertInstanceOf(ManageablePartnerEvent::class, $resolved);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveUsingBoxThrowsUnprocessableManageableProductTypeException(): void
    {
        $this->productRequest->type = 'Invalid-type';
        $this->manageableProductRequest->setProductRequest($this->productRequest->reveal());
        $this->manageableProductRequest
            ->getProductRequest()
            ->shouldBeCalled()
            ->willReturn($this->productRequest->reveal())
        ;
        $this->manageableProductRequest->getPartnerRequest()->willReturn(null);
        $this->expectException(UnprocessableManageableProductTypeException::class);
        $manageableProductResolver = new ManageableProductResolver();
        $manageableProductResolver->resolve($this->manageableProductRequest->reveal());
    }

    /**
     * @see testResolveUsingBoxSuccessfully
     */
    public function boxTypeProvider(): array
    {
        return [
            ['mev'],
            ['dev'],
            ['mlv'],
        ];
    }

    /**
     * @see testResolveUsingProductRelationshipSuccessfully
     */
    public function productRelationshipProvider(): array
    {
        return [
            ['box-experience'],
            ['experience-component'],
        ];
    }
}
