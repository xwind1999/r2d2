<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\Manageable;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Event\Manageable\ManageableBoxEvent;
use App\Event\Manageable\ManageableBoxExperienceEvent;
use App\Event\Manageable\ManageableComponentEvent;
use App\Event\Manageable\ManageableExperienceComponentEvent;
use App\Event\Manageable\ManageableExperienceEvent;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Contract\Request\Manageable\ManageableProductRequest
 */
class ManageableProductRequestTest extends TestCase
{
    /**
     * @covers \App\Event\Manageable\ManageableBoxEvent::fromBox
     * @covers ::getContext
     */
    public function testFromBoxAndGetContext()
    {
        $boxGoldenId = '12345';
        $manageableProductRequest = ManageableBoxEvent::fromBox($boxGoldenId);
        $expected = [
            'box_golden_id' => $boxGoldenId,
            'component_golden_id' => '',
            'experience_golden_id' => '',
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers \App\Event\Manageable\ManageableComponentEvent::fromComponent
     * @covers ::getContext
     */
    public function testFromComponentAndGetContext()
    {
        $componentGoldenId = '54321';
        $manageableProductRequest = ManageableComponentEvent::fromComponent($componentGoldenId);
        $expected = [
            'box_golden_id' => '',
            'component_golden_id' => $componentGoldenId,
            'experience_golden_id' => '',
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers \App\Event\Manageable\ManageableExperienceEvent::fromExperience
     * @covers ::getContext
     */
    public function testFromExperienceAndGetContext()
    {
        $experienceGoldenId = '84644';
        $manageableProductRequest = ManageableExperienceEvent::fromExperience($experienceGoldenId);
        $expected = [
            'box_golden_id' => '',
            'component_golden_id' => '',
            'experience_golden_id' => $experienceGoldenId,
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers \App\Event\Manageable\ManageableExperienceComponentEvent::fromExperienceComponent
     * @covers ::getContext
     */
    public function testFromExperienceComponentAndGetContext()
    {
        $componentGoldenId = '54321';
        $experienceGoldenId = '34512';
        $manageableProductRequest = ManageableExperienceComponentEvent::fromExperienceComponent(
            $experienceGoldenId,
            $componentGoldenId
        );
        $expected = [
            'box_golden_id' => '',
            'component_golden_id' => $componentGoldenId,
            'experience_golden_id' => $experienceGoldenId,
        ];
        $this->assertEquals($expected, $manageableProductRequest->getContext());
    }

    /**
     * @covers \App\Event\Manageable\ManageableBoxExperienceEvent::fromBoxExperience
     * @covers ::getContext
     */
    public function testFromBoxExperienceAndGetContext()
    {
        $boxGoldenId = '12345';
        $experienceGoldenId = '34512';
        $manageableProductRequest = ManageableBoxExperienceEvent::fromBoxExperience($boxGoldenId, $experienceGoldenId);
        $expected = [
            'box_golden_id' => $boxGoldenId,
            'component_golden_id' => '',
            'experience_golden_id' => $experienceGoldenId,
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
        $manageableProductRequest->setProductRequest($productRequest->reveal());
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
        $manageableProductRequest->setProductRelationshipRequest($productRelationshipRequest->reveal());
        $this->assertInstanceOf(
            ProductRelationshipRequest::class,
            $manageableProductRequest->getProductRelationshipRequest()
        );
    }
}
