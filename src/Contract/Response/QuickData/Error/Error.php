<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData\Error;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Error
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
     * @JMS\SerializedName("FieldName")
     */
    public string $fieldName;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("Message")
     */
    public string $message;
}
