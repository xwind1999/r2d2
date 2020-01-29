<?php

declare(strict_types=1);

namespace App\Exception\Http;

class BadRequestException extends HttpException
{
    protected const MESSAGE = 'Bad request';
    protected const CODE = 1000001;
    protected const HTTP_CODE = 400;
}
