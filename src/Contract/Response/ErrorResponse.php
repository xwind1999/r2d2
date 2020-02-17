<?php

declare(strict_types=1);

namespace App\Contract\Response;

class ErrorResponse
{
    public const DEFAULT_MESSAGE = 'General error';

    public const DEFAULT_CODE = 1000000;

    protected string $message = self::DEFAULT_MESSAGE;

    protected int $code = self::DEFAULT_CODE;

    protected array $errorList = [];

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getErrorList(): array
    {
        return $this->errorList;
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

    public function setErrorList(array $errorList): self
    {
        $this->errorList = $errorList;

        return $this;
    }

    public function toArray(): array
    {
        $errorList = [];
        if (count($this->errorList) > 0) {
            $errorList = ['errors' => $this->errorList];
        }

        return [
            'error' => array_merge([
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ], $errorList),
        ];
    }
}
