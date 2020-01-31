<?php

declare(strict_types=1);

namespace App\Exception\Http;

class UnprocessableEntityException extends ApiException
{
    protected const MESSAGE = 'Unprocessable entity';
    protected const CODE = 1000002;
    protected const HTTP_CODE = 422;
}
