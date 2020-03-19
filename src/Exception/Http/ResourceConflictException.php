<?php

declare(strict_types=1);

namespace App\Exception\Http;

class ResourceConflictException extends ApiException
{
    protected const MESSAGE = 'Resource already exists';
    protected const CODE = 1000004;
    protected const HTTP_CODE = 409;
}
