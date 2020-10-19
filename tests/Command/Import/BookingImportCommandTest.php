<?php

declare(strict_types=1);

namespace App\Tests\Command\Import;

use App\Command\Import\BookingImportCommand;
use App\Contract\Request\Booking\BookingImport\BookingImportRequest;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \App\Command\Import\BookingImportCommand
 */
class BookingImportCommandTest extends AbstractImportCommandTest
{
    protected ObjectProphecy $partnerRequest;
    protected ObjectProphecy $requestClass;

    protected function setUp(): void
    {
        $this->requestClass = $this->prophesize(BookingImportRequest::class);
        parent::setUp();

        $this->command = new BookingImportCommand(
            $this->logger->reveal(),
            $this->messageBus->reveal(),
            $this->helper->reveal(),
            $this->validator->reveal(),
            $this->serializer->reveal()
        );

        $this->commandTester = new CommandTester($this->command);
        $this->application->add($this->command);
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers \App\Contract\Request\Booking\BookingImport\BookingImportRequest
     * @covers \App\Contract\Request\Booking\BookingImport\Guest
     * @dataProvider requestProvider
     */
    public function testExecuteWithInvalidData(\Iterator $bookingImportRequest): void
    {
        $this->executeWithInvalidData($bookingImportRequest);

        $this->assertEquals('r2d2:booking:import', $this->command::getDefaultName());
    }

    /**
     * @covers ::__construct
     * @covers ::configure
     * @covers ::execute
     * @covers ::process
     * @covers \App\Contract\Request\Booking\BookingImport\BookingImportRequest
     * @covers \App\Contract\Request\Booking\BookingImport\Guest
     * @dataProvider requestProvider
     */
    public function testExecuteWithValidData(\Iterator $bookingImportRequest): void
    {
        $this->executeWithValidData($bookingImportRequest);

        $this->assertEquals('r2d2:booking:import', $this->command::getDefaultName());
        $this->messageBus
            ->dispatch(Argument::type(BookingImportRequest::class))
            ->shouldBeCalledTimes(count($bookingImportRequest));
    }

    public function requestProvider(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'goldenId' => '16503',
                'boxId' => '16503',
                'experienceId' => '16503',
                'experiencePrice' => '1650',
                'voucher' => '555444333',
                'currency' => 'EUR',
                'arrivalDate' => '2020-01-01 12:00:00',
                'endDate' => '2020-01-02 12:00:00',
                'additionalComment' => 'i would like a sea view, please',
                'customerData' => '{"prefix":"Sig.ra","firstname":"QaAntonette","lastname":"QaAntonette","email":"hassie20201014111809.2700792.a3cef62d@mailosaur.in","telephone":"323456789","country":"IT","country_code":"IT","customer_id":"2239572","address":{"prefix":"Sig.ra","firstname":"QaAntonette","lastname":"QaAntonette","email":"hassie20201014111809.2700792.a3cef62d@mailosaur.in","street1":"6 Accacia Avenue","additionToAddress":"","postcode":"12345","city":"Nancy","country":"IT","phone":"323456789","alternativePhone":"","region":""},"deliveryAddress":[],"language":"it-IT"}',
                'components' => '{"id":1246196,"name":"Domus Hyblaea Resort****","description":"Immerso nel cuore della Val di Noto, il Domus Hyblaea Resort vi attende in questo angolo di Sicilia per una vacanza di totale relax a pochi passi da Siracusa. Questa struttura a quattro stelle, adagiata in un magnifico uliveto e frutteto, vanta camere arredate in stile moderno, un ristorante che vi conquister\u00e0 con gli antichi sapori della cucina siciliana e aree benessere private dove potrete ritemprarvi da capo a piedi.","components":[["due notti in camera Classic Deluxe con area relax esterna privata","due colazioni a Buffet","una guida tascabile"]],"universe":{"code":"STA","name":"Soggiorni"},"gallery":{"items":["https:\/\/media.smartbox.com\/pim\/10000019308411915212188.jpg","https:\/\/media.smartbox.com\/pim\/1000001930860450275996.jpg","https:\/\/media.smartbox.com\/pim\/10000019308481115682548.jpg"]},"practicalInfo":{"openingDates":"Accedi al tuo account e verifica le date disponibili.","text":"Accesso disabili - Internet \/ Wifi - Animali ammessi (su richiesta e con supplemento) - Possibilit\u00e0 di letto e culla aggiuntivi (con supplemento) - Arrivo consigliato dalle 13:30 - Check out entro le 11:00.","location":"Siracusa: 40 km (40min)\nGela: 100 km (1h 30min)","onSite":""},"experienceAvailabilityRestrictions":"","bookingFlow":"default","reservable":true,"rating":null,"reviewsCount":0,"showRating":false,"ratingClass":null,"personCount":"2","partner":{"id":"00680696","name":"Domus Hyblaea Resort****","address":"Via G. Campailla","email":"sara.tine@domushyblaea.it","phone":"346 841 1331","phoneInternational":"+39 3468411331","description":"Immerso nel cuore della Val di Noto, il Domus Hyblaea Resort vi attende in questo angolo di Sicilia per una vacanza di totale relax a pochi passi da Siracusa. Questa struttura a quattro stelle, adagiata in un magnifico uliveto e frutteto, vanta camere arredate in stile moderno, un ristorante che vi conquister\u00e0 con gli antichi sapori della cucina siciliana e aree benessere private dove potrete ritemprarvi da capo a piedi.","supplementalCharges":"","country":{"code":"IT","name":"Italia"},"region":{"code":"IT-82","name":"Sicilia"},"subRegion":{"code":"IT-SR","name":"Siracusa"},"city":"Palazzolo Acreide","postCode":"96010","website":"","gps":{"longitude":14.913047799999999,"latitude":37.0705685},"ceaseDate":null,"preferredContactMethod":"email","components":[["due notti in camera Classic Deluxe con area relax esterna privata","due colazioni a Buffet","una guida tascabile"]],"language":"it","touristTax":false,"certification":[]},"buyerSmartValue":1.3500000000000001,"beneficiarySmartValue":1.3500000000000001,"product":{"id":847825,"price":"199.90","name":"3 giorni dincanto in Europa","shortDescription":"2 notti con colazione per 2 persone","gallery":{"packshot":"https:\/\/media.smartbox.com\/pim\/1000001706552543894719.png"},"legacyCode":null,"isB2B":false,"webExclusive":false,"redirectTo":null,"voucherValidity":{"type":"relative","months":39,"date":null}},"specialOffers":[],"facets":[],"price":{"amount":199.90000000000001,"currency":"EUR"},"cancellationPolicyDays":3}',
                'roomPrice' => '400.00;1650',
                'roomType' => 'extra_night;oob',
                'beginRoomDate' => '2020-01-01 00:00:00;2020-01-01 00:00:00',
                'endRoomDate' => '2020-01-02 12:00:00;2020-01-02 00:00:00',
            ],
        ]);

        yield [$records];
    }

    public function requestProviderInvalidData(): ?\Generator
    {
        $records = new \ArrayIterator([
            [
                'id' => '9999999999999999999999',
                'type' => 'partner',
                'partnerCeaseDate' => new \DateTime('now'),
                'isChannelManagerEnabled' => '',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
            [
                'id' => '9999999999999999999999',
                'type' => 'partner',
                'partnerCeaseDate' => null,
                'isChannelManagerEnabled' => '',
                'updatedAt' => '2020-01-01 00:00:00',
            ],
            [
                'id' => '9999999999999999999999',
                'type' => 'partner',
                'partnerCeaseDate' => null,
                'isChannelManagerEnabled' => '',
                'updatedAt' => null,
            ],
        ]);

        yield [$records];
    }

    /**
     * @covers::configure
     */
    public function testConfigureOutput()
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('Command to push CSV booking to the queue', $this->command->getDescription());
        $this->assertArrayHasKey('file', $definition->getArguments());
        $this->assertEquals('CSV file path', $definition->getArgument('file')->getDescription());
    }
}
