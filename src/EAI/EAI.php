<?php

declare(strict_types=1);

namespace App\EAI;

use App\Contract\Request\EAI\RoomRequest;
use Smartbox\ApiRestClient\ApiRestException;
use Smartbox\ApiRestClient\Clients\EaiV0Client;

class EAI
{
    protected EaiV0Client $eaiClient;

    public function __construct(EaiV0Client $eaiClient)
    {
        $this->eaiClient = $eaiClient;
    }

    /**
     * @throws ApiRestException
     */
    public function pushRoom(RoomRequest $roomTypeProduct): void
    {
        $this->eaiClient->sendRoomTypeProductInformation($roomTypeProduct);
    }
}
