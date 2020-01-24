<?php

declare(strict_types=1);

namespace App\Contract\Response;

class ErrorResponse
{
    protected string $message = 'General error';

    protected int $code = 1000000;

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'error' => [
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ],
        ];
    }
}
