<?php

declare(strict_types=1);

namespace App\EAI;

use App\Contract\Request\EAI\RoomRequest;
use GuzzleHttp\Exception\GuzzleException;
use Smartbox\ApiRestClient\ApiRestException;
use Smartbox\ApiRestClient\ApiRestResponse;
use Smartbox\ApiRestClient\Clients\EaiV0Client;
use Smartbox\CDM\Entity\Booking\ChannelManagerBooking;

class EAI
{
    private const URI_CRS_BOOKING = '/api/rest/eai/v0/crs_booking';
    private EaiV0Client $eaiClient;

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

    /**
     * @throws GuzzleException
     */
    public function pushChannelManagerBooking(ChannelManagerBooking $channelManagerBooking): ApiRestResponse
    {
        return $this->eaiClient->request('POST', self::URI_CRS_BOOKING, $channelManagerBooking);
    }
}
