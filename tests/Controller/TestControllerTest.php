<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestControllerTest extends WebTestCase
{
    /**
     * @dataProvider providerEcho
     */
    public function testEcho(string $request, string $response, int $httpCode)
    {
        $client = static::createClient();
        $client->request('POST', '/api/tests/echo', [], [], [], $request);
        $this->assertEquals($response, $client->getResponse()->getContent());
        $this->assertEquals($httpCode, $client->getResponse()->getStatusCode());
    }

    /**
     * @see testEcho
     */
    public function providerEcho(): iterable
    {
        yield 'happy path' => [
            json_encode(['message' => 'abcd']),
            json_encode(['message' => 'abcd']),
            200,
        ];

        yield 'unprocessable' => [
            json_encode(['messagexxxxx' => 'abcd']),
            json_encode(['error' => ['message' => 'Unprocessable entity', 'code' => 1000002]]),
            422,
        ];

        yield 'bad request' => [
            '{,}',
            json_encode(['error' => ['message' => 'Bad request', 'code' => 1000001]]),
            400,
        ];
    }
}
