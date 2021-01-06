<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\EAI;

use App\Constraint\BookingChannelConstraint;
use App\Constraint\BookingStatusConstraint;
use App\Constraint\CMHStatusConstraint;
use App\Contract\Request\EAI\ChannelManagerBookingRequest;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Guest;
use App\Entity\Partner;
use App\Event\Booking\BookingStatusEvent;
use App\Tests\ProphecyTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \App\Contract\Request\EAI\ChannelManagerBookingRequest
 */
class ChannelManagerBookingRequestTest extends ProphecyTestCase
{
    private Booking $booking;
    private ChannelManagerBookingRequest $request;

    public function setUp(): void
    {
        $this->bookingUpdatedEvent = $this->prophesize(BookingStatusEvent::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->booking = new Booking();
        $this->booking->voucher = '198257918';
        $this->booking->goldenId = '12345';
        $this->booking->status = BookingStatusConstraint::BOOKING_STATUS_COMPLETE;
        $dateTime = new \DateTime('2020-08-05 16:58:11.455209');
        $this->booking->startDate = $dateTime;
        $this->booking->endDate = $dateTime;
        $this->booking->createdAt = $dateTime;
        $this->booking->updatedAt = $dateTime;
        $this->booking->voucher = '1234154';
        $this->booking->partnerGoldenId = '1234154';
        $this->booking->country = 'FR';
        $this->booking->totalPrice = 1212;
        $this->booking->experienceGoldenId = '1234154';
        $this->booking->components = [
            'name' => 'name',
        ];
        $this->booking->currency = 'EUR';
        $this->booking->lastStatusChannel = BookingChannelConstraint::BOOKING_LAST_STATUS_CHANNEL_PARTNER;

        $experienceComponent = $this->prophesize(ExperienceComponent::class);
        $component = $this->prophesize(Component::class);
        $component->goldenId = '5464';
        $component->name = 'component name';
        $experienceComponent->component = $component->reveal();

        $boxExperience = $this->prophesize(BoxExperience::class);
        $box = $this->prophesize(Box::class);
        $box->country = 'FR';
        $boxExperience->box = $box->reveal();

        $experience = $this->prophesize(Experience::class);
        $experience->price = 125;
        $experience->currency = 'EUR';
        $experience->experienceComponent = new ArrayCollection([$experienceComponent->reveal()]);
        $experience->boxExperience = new ArrayCollection([$boxExperience->reveal()]);
        $experience->peopleNumber = 4;
        $this->booking->experience = $experience->reveal();

        $partner = $this->prophesize(Partner::class);
        $partner->currency = 'EUR';
        $this->booking->partner = $partner->reveal();

        $bookingDateDayFirst = $this->prophesize(BookingDate::class);
        $bookingDateDayFirst->componentGoldenId = '5464';
        $bookingDateDayFirst->component = $component;
        $bookingDateDayFirst->date = $dateTime;
        $bookingDateDayFirst->price = 606;
        $bookingDateDayFirst->isExtraNight = true;
        $bookingDateDayFirst->isExtraRoom = true;

        $bookingDateDaySecond = $this->prophesize(BookingDate::class);
        $bookingDateDaySecond->componentGoldenId = '8794';
        $bookingDateDaySecond->component = $component;
        $bookingDateDaySecond->date = $dateTime;
        $bookingDateDaySecond->price = 706;
        $bookingDateDaySecond->isExtraNight = true;
        $bookingDateDaySecond->isExtraRoom = false;

        $bookingDateDayThird = $this->prophesize(BookingDate::class);
        $bookingDateDayThird->componentGoldenId = '8794';
        $bookingDateDayThird->component = $component;
        $bookingDateDayThird->date = $dateTime;
        $bookingDateDayThird->price = 806;
        $bookingDateDayThird->isExtraNight = false;
        $bookingDateDayThird->isExtraRoom = false;

        $bookingDateDayFourth = $this->prophesize(BookingDate::class);
        $bookingDateDayFourth->componentGoldenId = '8794';
        $bookingDateDayFourth->component = $component;
        $bookingDateDayFourth->date = $dateTime;
        $bookingDateDayFourth->price = 906;
        $bookingDateDayFourth->isExtraNight = false;
        $bookingDateDayFourth->isExtraRoom = true;

        $this->booking->bookingDate = new ArrayCollection(
            [
                $bookingDateDayFirst->reveal(),
                $bookingDateDaySecond->reveal(),
                $bookingDateDayThird->reveal(),
                $bookingDateDayFourth->reveal(),
            ]
        );

        $guest = $this->prophesize(Guest::class);
        $guest->firstName = 'First Name';
        $guest->lastName = 'Last Name';
        $guest->phone = '089 585 5555';
        $guest->email = 'teste@teste.com';
        $this->booking->guest = new ArrayCollection([$guest->reveal()]);

        $this->request = new ChannelManagerBookingRequest();
    }

    /**
     * @covers ::fromCompletedBooking
     * @covers ::fromCancelledBooking
     * @covers ::fromRejectedBooking
     * @covers ::createChannelManagerBookingRequest
     * @dataProvider cmhStatusProvider
     */
    public function testFromBooking(string $cmhStatus): void
    {
        $result = null;
        switch ($cmhStatus) {
            case CMHStatusConstraint::BOOKING_STATUS_CONFIRMED:
                $result = $this->request::fromCompletedBooking($this->booking);
                break;

            case CMHStatusConstraint::BOOKING_STATUS_CANCELLED:
                $result = $this->request::fromCancelledBooking($this->booking);
                break;

            case CMHStatusConstraint::BOOKING_STATUS_REJECTED:
                $result = $this->request::fromRejectedBooking($this->booking);
                break;
        }

        $guests = $result->getRooms()[0]->getGuests();
        $primaryGuest = [];
        foreach ($guests as $guest) {
            if ($guest->isPrimary()) {
                $primaryGuest[] = $guest;
            }
        }

        $this->assertEquals($cmhStatus, $result->getStatus());
        $this->assertEquals($this->booking->goldenId, $result->getId());
        $this->assertEquals($this->booking->startDate, $result->getStartDate());
        $this->assertEquals($this->booking->endDate, $result->getEndDate());
        $this->assertEquals($this->booking->createdAt, $result->getCreatedAt());
        $this->assertEquals($this->booking->updatedAt, $result->getUpdatedAt());
        $this->assertEquals($this->booking->voucher, $result->getVoucher()->getId());
        $this->assertEquals($this->booking->partnerGoldenId, $result->getPartner()->getId());
        $this->assertEquals($this->booking->experienceGoldenId, $result->getExperience()->getId());
        $this->assertEquals($this->booking->experience->price, $result->getExperience()->getPrice()->getAmount());
        $this->assertEquals($this->booking->totalPrice, $result->getTotalPrice()->getAmount());
        $this->assertEquals(1, count($primaryGuest));
        $this->assertEquals($this->booking->experience->peopleNumber, count($result->getRooms()[0]->getGuests()));
        $this->assertEquals($this->booking->lastStatusChannel, $result->getLastStatusChannel());
    }

    /**
     * @covers ::fromCompletedBooking
     * @covers ::createChannelManagerBookingRequest
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $result = $this->request::fromCompletedBooking($this->booking);

        $dateTime = new \DateTime('2020-08-05 16:58:11.455209');
        $expected = [
            'booking_golden_id' => 12345,
            'booking_status' => 'confirmed',
            'booking_total_amount' => 1212,
            'booking_currency_code' => 'EUR',
            'booking_start_date' => $dateTime,
            'booking_end_date' => $dateTime,
            'booking_created_at' => $dateTime,
            'booking_updated_at' => $dateTime,
            'voucher_golden_id' => 1234154,
            'partner_golden_id' => 1234154,
            'experience_golden_id' => 1234154,
            'experience_total_price' => 125,
            'experience_currency' => 'EUR',
            'last_status_channel' => 'partner',
        ];

        $this->assertEquals($expected, $result->getContext());
    }

    public function testGetEventName(): void
    {
        $request = new ChannelManagerBookingRequest();
        $request->setStatus('complete');
        $this->assertEquals('complete booking pushed to EAI', $request->getEventName());
    }

    /**
     * @see testFromBooking
     */
    public function cmhStatusProvider(): array
    {
        return [
            ['confirmed'],
            ['cancelled'],
            ['rejected'],
        ];
    }
}
