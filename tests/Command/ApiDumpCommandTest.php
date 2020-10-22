<?php

declare(strict_types=1);

namespace App\Tests\Command {
    use App\Command\ApiDumpCommand;
    use App\Tests\ProphecyKernelTestCase;
    use Nelmio\ApiDocBundle\ApiDocGenerator;
    use OpenApi\Annotations\OpenApi;
    use Prophecy\Argument;
    use Prophecy\Prophecy\ObjectProphecy;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Tester\CommandTester;

    class ApiDumpCommandTest extends ProphecyKernelTestCase
    {
        /**
         * @var Application
         */
        protected $application;

        /**
         * @var ApiDumpCommand
         */
        protected $command;

        /**
         * @var ApiDocGenerator|ObjectProphecy
         */
        protected $apiDocGenerator;

        public function setUp(): void
        {
            $kernel = static::createKernel();
            $this->application = new Application($kernel);
            $this->apiDocGenerator = $this->prophesize(ApiDocGenerator::class);
            $this->command = new ApiDumpCommand($this->apiDocGenerator->reveal());

            $this->application->add($this->command);
        }

        public function testExecuteNoPretty(): void
        {
            $commandTester = new CommandTester($this->command);

            $openApi = $this->prophesize(OpenApi::class);
            $openApi->toJson(0)->willReturn('{"test":"bbbb"}');

            $this->apiDocGenerator->generate()->willReturn($openApi->reveal());

            $commandTester->execute(['--no-pretty']);

            $output = $commandTester->getDisplay();
            $this->assertEquals("{\"test\":\"bbbb\"}\n", $output);
        }

        public function testExecutePretty(): void
        {
            $commandTester = new CommandTester($this->command);

            $openApi = $this->prophesize(OpenApi::class);
            $openApi->toJson(128)->willReturn("{\n\"test\": \"bbbb\"\n}");

            $this->apiDocGenerator->generate()->willReturn($openApi->reveal());

            $commandTester->execute([]);

            $output = $commandTester->getDisplay();
            $this->assertEquals("{\n\"test\": \"bbbb\"\n}\n", $output);
            $this->assertEquals(0, $commandTester->getStatusCode());
        }

        public function testExecuteWillFail(): void
        {
            $commandTester = new CommandTester($this->command);

            $openApi = $this->prophesize(OpenApi::class);
            $openApi->toJson(Argument::any())->willReturn(false);

            $this->apiDocGenerator->generate()->willReturn($openApi->reveal());

            $commandTester->execute([]);

            $this->assertEquals(1, $commandTester->getStatusCode());
        }

        public function testCorrectDescription(): void
        {
            $this->assertEquals('Dumps API documentation in Swagger JSON format', $this->command->getDescription());
        }

        public function testHasValidOptions(): void
        {
            $this->assertInstanceOf(InputOption::class, $this->command->getDefinition()->getOption('no-pretty'));
        }
    }
}

namespace Nelmio\ApiDocBundle {
    class ApiDocGenerator
    {
        public function generate()
        {
        }
    }
}

namespace EXSyst\Component\Swagger {
    class Swagger
    {
        public function toArray()
        {
        }
    }
}
