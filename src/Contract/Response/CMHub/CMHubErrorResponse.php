<?php

declare(strict_types=1);

namespace App\Contract\Response\CMHub;

use JMS\Serializer\Annotation as JMS;

class CMHubErrorResponse extends CMHubResponse
{
    /**
     * @JMS\Type("strict_integer")
     */
    public int $httpCode = 405;

    /**
     * @JMS\Type("string")
     */
    public string $message;
}
