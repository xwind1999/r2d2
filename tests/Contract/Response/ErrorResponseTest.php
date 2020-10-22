<?php

declare(strict_types=1);

namespace App\Tests\Contract\Response;

use App\Contract\Response\ErrorResponse;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Contract\Response\ErrorResponse
 */
class ErrorResponseTest extends ProphecyTestCase
{
    /**
     * @covers ::toArray
     */
    public function testToArraySuccesfully(): void
    {
        $errorResponse = new ErrorResponse();
        $errorResponse->errorList = ['this is something that happened'];
        $message = 'test message';
        $code = 1112223;
        $expected = [
            'error' => [
                'message' => $message,
                'code' => $code,
                'errors' => [
                    'this is something that happened',
                ],
            ],
        ];
        $errorResponse->message = $message;
        $errorResponse->code = $code;
        $this->assertEquals($expected, $errorResponse->toArray());
    }

    /**
     * @covers ::toArray
     */
    public function testToArrayWithNoErrors(): void
    {
        $errorResponse = new ErrorResponse();
        $message = 'test message';
        $code = 1112223;
        $expected = [
            'error' => [
                'message' => $message,
                'code' => $code,
            ],
        ];
        $errorResponse->message = $message;
        $errorResponse->code = $code;
        $this->assertEquals($expected, $errorResponse->toArray());
    }
}
