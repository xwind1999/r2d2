<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData;

use App\Contract\Response\QuickData\Error\ResponseStatus;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class QuickDataErrorResponse extends QuickDataResponse
{
    /**
     * @JMS\Exclude()
     */
    public int $httpCode = 405;

    /**
     * @Assert\Type(type="App\Contract\Response\QuickData\Error\ResponseStatus")
     *
     * @JMS\Type("App\Contract\Response\QuickData\Error\ResponseStatus")
     * @JMS\SerializedName("ResponseStatus")
     */
    public ResponseStatus $responseStatus;

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
