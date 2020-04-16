<?php

declare(strict_types=1);

namespace App\Resolver\Exception;

class NonExistentTypeResolverExcepetion extends \Exception
{
    protected const MESSAGE = 'Invalid type.';
}
