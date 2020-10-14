<?php

declare(strict_types=1);

namespace Symfony\Component\Finder {
    class Finder
    {
        public static iterable $generator;

        public function hasResults(): bool
        {
            return true;
        }

        public function in($dir): self
        {
            return $this;
        }

        public function files(): self
        {
            return $this;
        }

        public function name($whatever): iterable
        {
            return self::$generator;
        }
    }
}

namespace App\Exception\Test {
    class TestClass
    {
        public const MESSAGE = 'Test Message';
        public const CODE = '0000666';
    }

    class TestException extends \Exception
    {
        public const MESSAGE = 'Test Message2';
        public const CODE = '0000667';
    }
}

namespace App\Tests\Command {
    use App\Command\ExceptionDumpCommand;
    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
    use Symfony\Component\Console\Tester\CommandTester;
    use Symfony\Component\Finder\SplFileInfo;

    class ExceptionDumpCommandTest extends KernelTestCase
    {
        /**
         * @var Application
         */
        protected $application;

        /**
         * @var ExceptionDumpCommand
         */
        protected $command;

        public function setUp(): void
        {
            $kernel = static::createKernel();
            $this->application = new Application($kernel);
            $this->command = new ExceptionDumpCommand();

            $this->application->add($this->command);
        }

        public function testExecute(): void
        {
            $fileInfo1 = $this->prophesize(SplFileInfo::class);
            $fileInfo1->getRealPath()->willReturn('/app/src/Exception/Test/TestClass.php');
            $fileInfo1->getRelativePathname()->willReturn('Test/TestClass.php');

            $fileInfo2 = $this->prophesize(SplFileInfo::class);
            $fileInfo2->getRealPath()->willReturn('/app/src/Exception/Test/TestException.php');
            $fileInfo2->getRelativePathname()->willReturn('Test/TestException.php');

            $generator = [$fileInfo1->reveal(), $fileInfo2->reveal()];
            \Symfony\Component\Finder\Finder::$generator = $generator;
            $commandTester = new CommandTester($this->command);

            $expectedOutput =
                '| Code | Message | Class Name | Namespace |'.PHP_EOL.
                '| :--- | :--- | :--- | :--- |'.PHP_EOL.
                '| 0000667 | Test Message2 | TestException | App\Exception\Test |'.PHP_EOL
            ;

            $commandTester->execute([]);
            $this->assertEquals($expectedOutput, $commandTester->getDisplay());

            $this->assertEquals(0, $commandTester->getStatusCode());
        }

        public function testCorrectDescription(): void
        {
            $this->assertEquals('Dumps exception information', $this->command->getDescription());
        }
    }
}
