<?php

declare(strict_types=1);

namespace App\Manager;

class HelloWorldManager
{
    public const HELLO = 'Hello';
    public const WORLD = 'World';
    public const SPACE = ' ';

    public function create(): string
    {
        return $this->concatenate(self::HELLO, self::SPACE, self::WORLD);
    }

    public function concatenate(string ...$params): string
    {
        return implode('', $params);
    }
}
