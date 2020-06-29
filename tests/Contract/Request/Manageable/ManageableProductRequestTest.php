<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\Manageable;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Request\Manageable\ManageableProductRequest
 */
class ManageableProductRequestTest extends TestCase
{
    /**
     * @covers ::fromBox
     * @covers ::getContext
     */
    public function testFromBoxAndGetContext()
    {
        $boxGoldenId = '12345';
        $componentGoldenId = '54321';
        $experienceGoldenId = '34512';
        $manageableProductRequest = ManageableProductRequest::fromBox($boxGoldenId);
        $expected = [
            'box_golden_id' => $boxGoldenId,
            'componentGoldenId' => '',
            'experienceGoldenId' => '',
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers ::fromComponent
     * @covers ::getContext
     */
    public function testFromComponentAndGetContext()
    {
        $boxGoldenId = '12345';
        $componentGoldenId = '54321';
        $experienceGoldenId = '34512';
        $manageableProductRequest = ManageableProductRequest::fromComponent($componentGoldenId);
        $expected = [
            'box_golden_id' => '',
            'componentGoldenId' => $componentGoldenId,
            'experienceGoldenId' => '',
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers ::fromExperienceComponent
     * @covers ::getContext
     */
    public function testFromExperienceComponentAndGetContext()
    {
        $boxGoldenId = '12345';
        $componentGoldenId = '54321';
        $experienceGoldenId = '34512';
        $manageableProductRequest = ManageableProductRequest::fromExperienceComponent($componentGoldenId, $experienceGoldenId);
        $expected = [
            'box_golden_id' => '',
            'componentGoldenId' => $componentGoldenId,
            'experienceGoldenId' => $experienceGoldenId,
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers ::fromBoxExperience
     * @covers ::getContext
     */
    public function testFromBoxExperienceAndGetContext()
    {
        $boxGoldenId = '12345';
        $componentGoldenId = '54321';
        $experienceGoldenId = '34512';
        $manageableProductRequest = ManageableProductRequest::fromBoxExperience($boxGoldenId, $experienceGoldenId);
        $expected = [
            'box_golden_id' => $boxGoldenId,
            'componentGoldenId' => '',
            'experienceGoldenId' => $experienceGoldenId,
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers ::setProductRequest
     * @covers ::getProductRequest
     */
    public function testSetAndGetProductRequest()
    {
        $productRequest = $this->prophesize(ProductRequest::class);
        $manageableProductRequest = new ManageableProductRequest();
        $this->assertEmpty($manageableProductRequest->setProductRequest($productRequest->reveal()));
        $this->assertInstanceOf(ProductRequest::class, $manageableProductRequest->getProductRequest());
    }

    /**
     * @covers ::setProductRelationshipRequest
     * @covers ::getProductRelationshipRequest
     */
    public function testSetAndGetProductRelationshipRequest()
    {
        $productRelationshipRequest = $this->prophesize(ProductRelationshipRequest::class);
        $manageableProductRequest = new ManageableProductRequest();
        $this->assertEmpty($manageableProductRequest->setProductRelationshipRequest($productRelationshipRequest->reveal()));
        $this->assertInstanceOf(ProductRelationshipRequest::class, $manageableProductRequest->getProductRelationshipRequest());
    }
}
