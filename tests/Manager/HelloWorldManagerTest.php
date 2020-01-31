<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Manager\HelloWorldManager;
use PHPUnit\Framework\TestCase;

class HelloWorldManagerTest extends TestCase
{
    public function testHelloWorld(): void
    {
        $helloWorldManager = new HelloWorldManager();
        $hello = 'Hello';
        $space = ' ';
        $world = 'World';
        $this->assertEquals($hello.$space.$world, $helloWorldManager->create());
    }

    public function testConcatenate(): void
    {
        $helloWorldManager = new HelloWorldManager();
        $hello = 'Hello';
        $space = ' ';
        $world = 'World';
        $this->assertEquals($hello.$space.$world, $helloWorldManager->concatenate($hello, $space, $world));
    }
}
