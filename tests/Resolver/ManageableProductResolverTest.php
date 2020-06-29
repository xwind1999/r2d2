<?php

declare(strict_types=1);

namespace App\Tests\QuickData;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Exception\Resolver\UnprocessableManageableProductTypeException;
use App\Resolver\ManageableProductResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Resolver\ManageableProductResolver
 */
class ManageableProductResolverTest extends TestCase
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
