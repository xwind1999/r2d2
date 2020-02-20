<?php

declare(strict_types=1);

namespace App\Tests\Contract\Response;

use App\Contract\Response\ErrorResponse;
use PHPUnit\Framework\TestCase;

class ErrorResponseTest extends TestCase
{
    public function testToArray()
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
