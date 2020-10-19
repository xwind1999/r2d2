<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingImport;

use App\Contract\Request\Booking\BookingCreateRequest;

class BookingImportRequest extends BookingCreateRequest
{
    public function getContext(): array
    {
        return [
            'booking_golden_id' => $this->bookingId,
            'box_id' => $this->box,
            'experience_id' => $this->experience->id,
            'currency' => $this->currency,
            'voucher' => $this->voucher,
            'booking_start_date' => $this->startDate->format('Y-m-d'),
            'booking_end_date' => $this->endDate->format('Y-m-d'),
        ];
    }
}
