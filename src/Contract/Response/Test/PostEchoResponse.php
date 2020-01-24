<?php

declare(strict_types=1);

namespace App\Contract\Response\Test;

use App\Contract\ResponseContractInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class PostEchoResponse implements ResponseContractInterface
{
    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    protected string $message;

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
