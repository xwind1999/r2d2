<?php

declare(strict_types=1);

namespace App\Exception\Http;

class ResourceNotFoundException extends ApiException
{
    protected const MESSAGE = 'Resource Not Found';
    protected const CODE = 1000003;
    protected const HTTP_CODE = 404;
}
