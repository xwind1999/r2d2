<?php

declare(strict_types=1);

namespace App\Command;

use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ExceptionDumpCommand extends Command
{
    protected static $defaultName = 'r2d2:exception:dump';
    protected const EXCEPTION_DIR = '../Exception/';
    protected const BASE_NAMESPACE = 'App\\Exception\\';

    protected function configure(): void
    {
        $this
            ->setDescription('Dumps exception information')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dir = __DIR__.'/'.self::EXCEPTION_DIR;
        $finder = new Finder();
        $files = $finder->in($dir)->files()->name('/^.+\.php$/i');
        $classes = [];
        $data = [];
        if ($finder->hasResults()) {
            foreach ($files as $file) {
                $removephp = str_replace('.php', '', $file->getRelativePathname());
                $classes[] = self::BASE_NAMESPACE.str_replace('/', '\\', $removephp);
            }
        }

        /** @var class-string $classFQDN */
        foreach ($classes as $classFQDN) {
            $ref = new ReflectionClass($classFQDN);
            if (!$ref->isSubclassOf(\Exception::class)) {
                continue;
            }

            $message = $ref->getConstant('MESSAGE');
            $code = $ref->getConstant('CODE');
            $data[] = [$code, $message, $ref->getShortName(), $ref->getNamespaceName()];
        }

        usort($data, function ($a, $b) {
            return $a[0] <=> $b[0];
        });

        $output->writeln('| Code | Message | Class Name | Namespace |');
        $output->writeln('| :--- | :--- | :--- | :--- |');

        foreach ($data as $line) {
            $output->writeln(sprintf('| %s | %s | %s | %s |', ...$line));
        }

        return 0;
    }
}
