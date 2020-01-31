<?php

declare(strict_types=1);

namespace App\Contract\Response;

class ErrorResponse
{
    public const DEFAULT_MESSAGE = 'General error';

    public const DEFAULT_CODE = 1000000;

    protected string $message = self::DEFAULT_MESSAGE;

    protected int $code = self::DEFAULT_CODE;

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setMessage(?string $message): self
    {
        if (null !== $message) {
            $this->message = $message;
        }

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
