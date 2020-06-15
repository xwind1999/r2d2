<?php

declare(strict_types=1);

namespace App\Tests\Command {
    use App\Command\ApiDumpCommand;
    use EXSyst\Component\Swagger\Swagger;
    use Nelmio\ApiDocBundle\ApiDocGenerator;
    use Prophecy\Prophecy\ObjectProphecy;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Tester\CommandTester;

    class ApiDumpCommandTest extends KernelTestCase
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

            $swagger = $this->prophesize(Swagger::class);
            $swagger->toArray()->willReturn(['test' => 'bbbb']);

            $this->apiDocGenerator->generate()->willReturn($swagger->reveal());

            $commandTester->execute(['--no-pretty']);

            $output = $commandTester->getDisplay();
            $this->assertEquals("{\"test\":\"bbbb\"}\n", $output);
        }

        public function testExecutePretty(): void
        {
            $commandTester = new CommandTester($this->command);

            $swagger = $this->prophesize(Swagger::class);
            $swagger->toArray()->willReturn(['test' => 'bbbb']);

            $this->apiDocGenerator->generate()->willReturn($swagger->reveal());

            $commandTester->execute([]);

            $output = $commandTester->getDisplay();
            $this->assertEquals("{\n    \"test\": \"bbbb\"\n}\n", $output);
            $this->assertEquals(0, $commandTester->getStatusCode());
        }

        public function testExecuteWillFail(): void
        {
            $commandTester = new CommandTester($this->command);

            $swagger = $this->prophesize(Swagger::class);
            $swagger->toArray()->willReturn(fopen('php://memory', 'r'));

            $this->apiDocGenerator->generate()->willReturn($swagger->reveal());

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
