<?php

declare(strict_types=1);

namespace App\Exception;

use App\Helper\ContextualTrait;
use Clogger\ContextualInterface;

class ContextualException extends \Exception implements ContextualInterface
{
    use ContextualTrait;

    protected const MESSAGE = 'General exception';
    protected const CODE = 1000000;

    final public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $message = empty($message) ? static::MESSAGE : $message;
        $code = 0 === $code ? static::CODE : $code;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @param mixed $context
     *
     * @return static
     */
    public static function forContext($context, ?\Throwable $previous = null)
    {
        $exception = new static(static::MESSAGE, static::CODE, $previous);

        return $exception;
    }
}
