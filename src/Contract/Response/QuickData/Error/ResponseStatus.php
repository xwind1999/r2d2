<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData\Error;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ResponseStatus
{
    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("ErrorCode")
     */
    public string $errorCode;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("Message")
     */
    public string $message;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("StackTrace")
     */
    public string $stackTrace;

    /**
     * @Assert\Type(type="array<App\Contract\Response\QuickData\Error\Error>")
     *
     * @JMS\Type("array<App\Contract\Response\QuickData\Error\Error>")
     * @JMS\SerializedName("Errors")
     */
    public array $errors;
}
