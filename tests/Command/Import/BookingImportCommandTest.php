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
            $this->serializer->reveal(),
            $this->moneyHelper->reveal()
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
                'goldenId' => '54302',
                'boxId' => '54302',
                'experienceId' => '54302',
                'experiencePrice' => '54302',
                'voucher' => '555444333',
                'currency' => 'EUR',
                'arrivalDate' => '2020-02-02 12:00:00',
                'endDate' => '2020-02-05 12:00:00',
                'additionalComment' => 'i would like a sea view, please',
                'customerData' => '0x1F8B08000000000004006D904B6BC2401485CF4F916EAB41939AAA2B0BDD74E1AEBB223298A88198943CA0A5F8DFFB6572190A95615EF77EF79C99FBA3077DAA51AE930A7D71DB30774422F6297388376AD5A992D31572645E54726E198E4C06E3ACA2E4F49F7FE534F1B99E6C6D6C0EE170288D72A6DAB1969E5EEB89F9ACADCE818D7444E16A1A239BF38F0BD12A383E2AA132F16BAA582B94629452AB1B347AF88EB77F5BCD9BDEEF660FFE9E05EDBF5CEFDF5BFBBF369005DC482DB4C46F8EE31AF7B95538F299EFF9D0BD81FCD03E74AEE29F3DFB397815A8CFCCF1A65F02A11B60B2010000',
                'components' => '0x1F8B0800000000000400ED58DB6EDB4610DD4F21FCD2168D554BB6E3D84FB59DB83090B445ECA60F4D105014256D2B8B2C29C97602FF7BCF9C995D91925C2441E2024540F0B69C9DDBCEE52CDFBB2DE7DD00D723D775FBAE87EBAE3B708F303275A9BB7239BF6DB997789A60C4BBDA25EE0CF70A2353F70ED7C45DB81946FA38728CCF402F1C0678AB5D8611EF4A521498A1FCCEC12DF948AE8F3026F4B91B61CE14234255E25BC1ABC7FBCCBD7673B78323C799E04B8D6F1EE329CF04CF535CE7BC961811F9EF7014781FB86F30AFC473865191B7007D82E70C4701B9134AA956A464E4535093DAFC16F4CB687582B739CE19F9791BBF7243CE9AD1B2C4F8072B17D459DE6BEA3E857E29A807D454C6FF06CF947356EDCEDC981CAFA86DB03F771D8C3DA71F32932C6BE43922EBE5377834E5936A382165493B3DBCA66BAA76AACD1535CAE9B5893D9551964A99B4E80BB3B0A09CCC56C2AF4584ACC7907E983668C407B97948F954E6E93975116F4FA8F99C2B587044B4087A0E30B6E0936AB3A0C5F22EFA0C316F443B3B16B1352DA9E89BDC22AA00AF99CD4A3147F494D8F7CC235DC501233D618494666D6291ECB962C1CB81B224C711D747792CC8D137FC58317BD47B212B3A96811A63A5AD917CAB997F7FE0D8620E887F44FB59C3168D678D9CD4FC53328B5266E51CCF1A55225325055E193D91B622E3045FC52379AC0C81BA6F92BD457E1A5775615E4D2CA76A5CB7DC1B1C61BEE79A55F8A235EABDD93B8835EBC25DBA6393D8AE66178C84915920BCB6DC1DE946316A2B771BF97A7AE72A7A6F0B6B257A97183942E6FDC053F80F684507E3578CA2196DBCC188AE85D08927F549ACDFE1D1757BEEB13BC4B5EBDE5A6C8C2C7A26989D5396DAF2B965EFA2F21FE2DCBF47F69F9835FA42B2BB94BDC7F361EC56A9BB90B74FCBDFD2EA67A09F35A2FA4B58BD2EB987F3847C24CA65C6C3C8DDA35CA929E307962CE753AB6153487B18A90738CF68E7E081243ED928F18D5599929535B3BA2F917E0E6F0C59E3B4E214ACB1C14B4FAD7BD756BF8E894772AB9529FBC08CFD6D8955E6ACF7DA4F1616D9439397C4CE97C6AEEC89940ACAECB367FAD84784E686D55BA4FF448FA4D02BE02F6F5845E47F1B114FCD4E515256404485FB2E229514E736E88EC921748F297509FD4351DBEA8C73EB73153B8C5829AB90B8DFCDC6750953AE556AF84A7AC115FD59F3FDD37456BB0BA24C41A913D6AE342299C23C1C505017738FC825698D3EB6D1551997C41075A32BD62B7D2B4850243B8AB845BBF8987536671DD5759C18B26D63F14BC35805DE36A3F0D4D0ABC8DBE698220D91FCC2F0A13CFF6C486A420CF01A34C736AF8F7B4DFEABFC7AC6F339B5CE29738F58AF872C52BD45D70BEBC2613FB28E7A747F20A86B4E5CE02D9A430E788ECD0CE9CD2372F286963B1105F499B97F59F69DD16FD72659F085A0BB39119FEA5751AEC4A3E4419F2B2BD48A4573A351BF4F59098EB0EA1D548A307B41AC7D0D2EA73177854A296AAEE535AC6EF3B88FFF29FD5FC77AD1FCF616476DA83EB9F7CB36EAD8FAD731E37668569786BFC4CB6DADB7B0768146ABE794B44B3C3530BAD5CAB909AD7DCADE33858481AD4BF0C22B8BD2E7E4DEA7EF6AC3DD1227123FBB4022CA2167BD903AA8B3BD55E81F376461BDA243E812C10763C3C2C166C15A09119F44FF3E477A6BD4CD2AB7CCD9346AF43DB54D8C9F726BF3FABA03FFBA03FFBA03FF2F77E09BF084587DCA5A5A114385FAB4DCB3CF6D5D963BD0F6CEF61C3D7B53A53C37EEA26DE86541FF90F99BB96D37AA469BE725232DA3654BAE62579F6BF121BCCF70DFC4BB1D5D8177C66CB9BD87466BA4D6DCD396ACFDD84B94EA1A5FFAB1E72BCD358FCE47D7F091ED43D5C6092D1E91F33C6AD0C5D1610D164CB707F4F2D85097C64E93760F7A76B02F79C2E3006FBBD1FA9C919B47D42FF453E28D49DC3DE4FC9F52F169403F6864C99EE205F3624CDFA8D5CD0EF47FFE2F3461948E58934771CD7DE43F636E857C157C7D439A21B3467C1EF0DF1CF127B65CC4FDDF2BD2CCE35A77702A6D4E7B746FE5497DFB01F3B4C60FF02533D4D444463D44C80E2223507AC685DA7388A3837367634EBD60B5D5BA9A5B9F6DAF66A84C63EB094FFF05257457E220BF672D0BEE649AB48FEEA15C76AAF4A3E75586566E58997322D85E7CAE5B31BEF94F5EC91CF9CBAC0F68F5F3FC05E8DA3F2D3D0491497E777195BF5C72958A20FFDB240A4A43F17716B98AA932E8DAAE6ABF192FB54A22F704369FACC4EDB2DEC99FAC8C11573BFD47BA4E59996D72CF9809C55A043EA1FE0790DE331D17CC1EC53595C5B5279F59C3C3F25C46EDC38ACDA22EC2E9CAEAD5D8EACEAE55CAF03FA259F1EE62C729A9ADA7DC5FAC7254B172690518D28779A3A2BDD99043A2674A2D967B9643DB6BE9EE513B4E16BBD033ACC3CB46874AF935EC7443CEFCCAB8F59C27D5FB36DA77E7FE01ACBCB9DB6C1B0000',
                'roomPrice' => '',
                'roomType' => '',
                'beginRoomDate' => '',
                'endRoomDate' => '',
            ],
            [
                'goldenId' => '879784',
                'boxId' => '879784',
                'experienceId' => '879784',
                'experiencePrice' => '879784',
                'voucher' => '777788222',
                'currency' => 'EUR',
                'arrivalDate' => '2020-04-02 12:00:00',
                'endDate' => '2020-04-05 12:00:00',
                'additionalComment' => 'i would like a sea view, please',
                'customerData' => '0x1F8B08000000000004006D904B6BC2401485CF4F916EAB41939AAA2B0BDD74E1AEBB223298A88198943CA0A5F8DFFB6572190A95615EF77EF79C99FBA3077DAA51AE930A7D71DB30774422F6297388376AD5A992D31572645E54726E198E4C06E3ACA2E4F49F7FE534F1B99E6C6D6C0EE170288D72A6DAB1969E5EEB89F9ACADCE818D7444E16A1A239BF38F0BD12A383E2AA132F16BAA582B94629452AB1B347AF88EB77F5BCD9BDEEF660FFE9E05EDBF5CEFDF5BFBBF369005DC482DB4C46F8EE31AF7B95538F299EFF9D0BD81FCD03E74AEE29F3DFB397815A8CFCCF1A65F02A11B60B2010000',
                'components' => '0x1F8B0800000000000400ED58DB6EDB4610DD4F21FCD2168D554BB6E3D84FB59DB83090B445ECA60F4D105014256D2B8B2C29C97602FF7BCF9C995D91925C2441E2024540F0B69C9DDBCEE52CDFBB2DE7DD00D723D775FBAE87EBAE3B708F303275A9BB7239BF6DB997789A60C4BBDA25EE0CF70A2353F70ED7C45DB81946FA38728CCF402F1C0678AB5D8611EF4A521498A1FCCEC12DF948AE8F3026F4B91B61CE14234255E25BC1ABC7FBCCBD7673B78323C799E04B8D6F1EE329CF04CF535CE7BC961811F9EF7014781FB86F30AFC473865191B7007D82E70C4701B9134AA956A464E4535093DAFC16F4CB687582B739CE19F9791BBF7243CE9AD1B2C4F8072B17D459DE6BEA3E857E29A807D454C6FF06CF947356EDCEDC981CAFA86DB03F771D8C3DA71F32932C6BE43922EBE5377834E5936A382165493B3DBCA66BAA76AACD1535CAE9B5893D9551964A99B4E80BB3B0A09CCC56C2AF4584ACC7907E983668C407B97948F954E6E93975116F4FA8F99C2B587044B4087A0E30B6E0936AB3A0C5F22EFA0C316F443B3B16B1352DA9E89BDC22AA00AF99CD4A3147F494D8F7CC235DC501233D618494666D6291ECB962C1CB81B224C711D747792CC8D137FC58317BD47B212B3A96811A63A5AD917CAB997F7FE0D8620E887F44FB59C3168D678D9CD4FC53328B5266E51CCF1A55225325055E193D91B622E3045FC52379AC0C81BA6F92BD457E1A5775615E4D2CA76A5CB7DC1B1C61BEE79A55F8A235EABDD93B8835EBC25DBA6393D8AE66178C84915920BCB6DC1DE946316A2B771BF97A7AE72A7A6F0B6B257A97183942E6FDC053F80F684507E3578CA2196DBCC188AE85D08927F549ACDFE1D1757BEEB13BC4B5EBDE5A6C8C2C7A26989D5396DAF2B965EFA2F21FE2DCBF47F69F9835FA42B2BB94BDC7F361EC56A9BB90B74FCBDFD2EA67A09F35A2FA4B58BD2EB987F3847C24CA65C6C3C8DDA35CA929E307962CE753AB6153487B18A90738CF68E7E081243ED928F18D5599929535B3BA2F917E0E6F0C59E3B4E214ACB1C14B4FAD7BD756BF8E894772AB9529FBC08CFD6D8955E6ACF7DA4F1616D9439397C4CE97C6AEEC89940ACAECB367FAD84784E686D55BA4FF448FA4D02BE02F6F5845E47F1B114FCD4E515256404485FB2E229514E736E88EC921748F297509FD4351DBEA8C73EB73153B8C5829AB90B8DFCDC6750953AE556AF84A7AC115FD59F3FDD37456BB0BA24C41A913D6AE342299C23C1C505017738FC825698D3EB6D1551997C41075A32BD62B7D2B4850243B8AB845BBF8987536671DD5759C18B26D63F14BC35805DE36A3F0D4D0ABC8DBE698220D91FCC2F0A13CFF6C486A420CF01A34C736AF8F7B4DFEABFC7AC6F339B5CE29738F58AF872C52BD45D70BEBC2613FB28E7A747F20A86B4E5CE02D9A430E788ECD0CE9CD2372F286963B1105F499B97F59F69DD16FD72659F085A0BB39119FEA5751AEC4A3E4419F2B2BD48A4573A351BF4F59098EB0EA1D548A307B41AC7D0D2EA73177854A296AAEE535AC6EF3B88FFF29FD5FC77AD1FCF616476DA83EB9F7CB36EAD8FAD731E37668569786BFC4CB6DADB7B0768146ABE794B44B3C3530BAD5CAB909AD7DCADE33858481AD4BF0C22B8BD2E7E4DEA7EF6AC3DD1227123FBB4022CA2167BD903AA8B3BD55E81F376461BDA243E812C10763C3C2C166C15A09119F44FF3E477A6BD4CD2AB7CCD9346AF43DB54D8C9F726BF3FABA03FFBA03FFBA03FF2F77E09BF084587DCA5A5A114385FAB4DCB3CF6D5D963BD0F6CEF61C3D7B53A53C37EEA26DE86541FF90F99BB96D37AA469BE725232DA3654BAE62579F6BF121BCCF70DFC4BB1D5D8177C66CB9BD87466BA4D6DCD396ACFDD84B94EA1A5FFAB1E72BCD358FCE47D7F091ED43D5C6092D1E91F33C6AD0C5D1610D164CB707F4F2D85097C64E93760F7A76B02F79C2E3006FBBD1FA9C919B47D42FF453E28D49DC3DE4FC9F52F169403F6864C99EE205F3624CDFA8D5CD0EF47FFE2F3461948E58934771CD7DE43F636E857C157C7D439A21B3467C1EF0DF1CF127B65CC4FDDF2BD2CCE35A77702A6D4E7B746FE5497DFB01F3B4C60FF02533D4D444463D44C80E2223507AC685DA7388A3837367634EBD60B5D5BA9A5B9F6DAF66A84C63EB094FFF05257457E220BF672D0BEE649AB48FEEA15C76AAF4A3E75586566E58997322D85E7CAE5B31BEF94F5EC91CF9CBAC0F68F5F3FC05E8DA3F2D3D0491497E777195BF5C72958A20FFDB240A4A43F17716B98AA932E8DAAE6ABF192FB54A22F704369FACC4EDB2DEC99FAC8C11573BFD47BA4E59996D72CF9809C55A043EA1FE0790DE331D17CC1EC53595C5B5279F59C3C3F25C46EDC38ACDA22EC2E9CAEAD5D8EACEAE55CAF03FA259F1EE62C729A9ADA7DC5FAC7254B172690518D28779A3A2BDD99043A2674A2D967B9643DB6BE9EE513B4E16BBD033ACC3CB46874AF935EC7443CEFCCAB8F59C27D5FB36DA77E7FE01ACBCB9DB6C1B0000',
                'roomPrice' => '20.83',
                'roomType' => 'extra_night',
                'beginRoomDate' => '2020-04-04 12:00:00',
                'endRoomDate' => '2020-04-05 12:00:00',
            ],
            [
                'goldenId' => 'RESA-0012328931',
                'boxId' => '848354',
                'experienceId' => '500925',
                'experiencePrice' => '1600',
                'currency' => 'EUR',
                'voucher' => '644342044',
                'arrivalDate' => '2020-09-02 12:00:00',
                'endDate' => '2020-09-04 12:00:00',
                'additionalComment' => 'Nous serons accompagnchien ne couchant pas sur le lit.',
                'customerData' => '0x1F8B0800000000000400DD524D4FC3300C7D3FA5EA19A68D41299C006970010901379850A1DDA854D22A4D1168DA7FE7D9490B63979D51E424B69F3FF29C156234B028B040894F6AA7941B64C829EFB4C7D8A388D7A2858319EC82BCE2CD503EE87F4287315781947A8128C4568CD88EBCC68CD133DC0554415FC62C55F057B42C2916237628A7458D57BC713738A3AFC78F68ADA9F93C8E58896D02B2AF374682634C718223DE53D57D844477443A56F80AE8CBA1AF4DEFB3EAF990F537AED357D6FA464B64499C47A538E44A5939C141C00BC3B932DF72096EF5EF66D12A6B0597C324E44AD88B554E9DF6952B03115E682BF566F46695951FA64AE24BADF7C0FD7C8BBD38A01B7AA5EEE69C26AC3B5546FA6949BE7EDAF7DABDCC38C285D630DA73845D7EC8EE3F2D23434EF913FEE53D329BDB3FF11E6B9573FFE2DEBE564FAE3FDCC74A3FDB5C3C623E4CDB304BC77339E45F10BB1FFA5FE31B65462D8202040000',                'components' => '0x1F8B0800000000000400ED585B73D34614DE9FE2F10BA5B5436C27C4E4A910484B07068650FA40998E22C98EC01721D9495C26FFBDDFF9CED995E4284CA6B49D3E643496D7BB67CFFDEA2FAEEB3297E07DE8F6DD2E9E476E88550F3B0B17B9B94B79D6753FBBDFDD9A1013B7E756D89FB98E7B81EF8E7B0DC8C2C5589DB825F6976E8ADBA9FB9E4F97D812FC2E0153805E8EFB19A01686FB31307770A61406A03FC2AAC05E42FC33E0DFC6DDE3FEBD5BF225B457BCA1F8327C0BCD1CE74BBC3340C949C4DD24E04CA18F1CBB1BDC4FA98D05F1A47C5750BB81CBE6DD95DD48DD0ECE7E25E71D70BA04C512AB73ACF43B2257253915BE64BF8E49F67B3C119904E39FB7E22006A635F0A7764BCEEF614739291AB053B38AD77BECCE486D5ED38DD05FD18A734AE1F1889C4BECCD1B18456AB56E61368868D9C8F4E8ED7B0F94AEDBB1172C269CA438E9007F8653E152297B4962AC447F9E23A1B3E2AD05BDED337EAB159E91724EBD888FADA89BB9DD2C884BECABDAFA68708B2D6BB4E9A8CEAF7028762E6A728AB557D484BF3777A7F6FB9C74D4F633DE9E929BA8A1F90D2112F39232C0950D89BC4E16D4C9EA9A9DD587BC1D63EA45A334A60D73E255CD958CD1F778BA2D78537ACBB62C093939355D1C3574A174D6BC9753CF82A9BFE5B51F199F953D966645CF95444B1D53FD761AEEBDE45A74F7963B053D564E46CC0E33EA40747746EE64756AB1A9BA15E93FE0F194328BA4D2F2E217D35912F2E409683D36DE9A19F4644B42B556D75D11764A9966C4BE09B8337AE13C58A10B4EC53373EC1C02DF037E844602D808762CF14B33844872891DB5A9C0E58C5A590DC8877F86EE00EF11DEFB3839C0EF475CEDE1FD10FB3BE03607872AD5BFCBC101288EB1DA07F5313EBBF8BDE37E81B57EFA4FE83F84EC7BF88CB9BB8BEFFD9AFC1FCC5A39E327A64FC6B4DC73F3716FB925BD7B419F9DBAA7F4B4D4ECD8858F2C999F7CF68B98CFB6334CC9C89F84FC21396A3B5262FA878FBC663D68429E92A26434A176CE1A583023BE69CDCD87CC8D3E2BCD6A39276304F89C21525C32B245B2E756EF0A46A156C90778FF865B137CB4222C6807A177C9FC965036C1FF1D7E498CC5A4B58616251FD6E5F055F83E71BDDECA6DE721C635337C1D47B30E2C4D43A24BC9919A6BD7CCCE029FF044FCACEA14548E855528AF13C9E0316D5EEF735E309B1F5B2DAEF3D387DEC48A827516B43FC0D3719F70227A91BEA86375EA3E56AF80A52955C48CA5771FD203FCDD51E3AEF2287C9D588651FE9E306E7AE6795A857AA14216D448493F4B4D77DA5FF91C26F1B6044DF5F96362B830DC09EBB7974F39D09E40704BFFA3F542A0A52EAD594FBB56A714A39C8DA1E3211E7F5FFDF802788E68BF8579E298102533FB053CBC89E5260A47AC096588D3FAD91F78344A0AFA48FB491FD9A36B39422B8568B1C95B17FC7B18CD545AB1AAAC9F18DC2EFB61C947D2998F5AEBCAB776E6127D89D9C2CB3DA6879F5BFDF45DE34D1EA778524642C6CA7C6875516BB5E4C91F6909E56E66F1259C950DBE7CAEF6DA39B34EC46B63084EA4327440FB801132E6CEE8DA8D7A1EAAE2300ADCFD803BD2050C894FB00D98EB3DA6BB79E56E5EB99B57FEEFF34A55DD2B6F17CF3C3229A7B59EAB9A703413D7FBEC660F7F8C7AD196698FCD1362EE5C851A34AD6587766C7DBCDB301E19CF82A5EFDE9177AFFD17D6C954B44A4E556F6E49710F99A98DA6C7DBA7D7F4A9ABB43689C4B4C1264C2DC2D32935EF33BD46FB8A55ADA22AF446B499425DE0E4941E5E7518177C76FE462D98B2E3F7F27A8F13DCEBC0C310777639B98CC0CD1EDEDA8B45366556907BC8F73B84D039601C644F59FFD3D0B10BF4825DCB2C74FE12D9DA11493778D4A8732FA9D5336A4665AE57B1BB19FBEB33F68CF13505BE88B1AB1A9CD03B75D6D09C91D1FFDE02EA923013725BBA3458B1A02C139BCFEAF5FB7D98E26422DA10F624CC8C1A836BA33DA0478D0C5AA5569C19E137B7BAA9555B2689D83AC07A9737E6A4AB13AF87CE2CC788F403CEA43BEC01DBE2F9257B2EF503ADF3DB3E545EABB2339321650E4F43649766A515FCFFE6FE67D0F0CF6A5EBBADAFA55663AEFB538F1D5993F336FCE5572994818670BAA4770E6FA05806886F93AA776384A4862F0E1DA39C8F4DB635EBA264409FE7DAFE07CA79FF93D9C74F11FFFC3F2103F6C143AEF43382F7ED83DB01FB6599BE729BA2AE2C6253F21C83DB7A3DA8E74C89D627C0FAA42556B54A3C037731A3A7B4FFD8AE43FAD9BBA05D56FC0F659B927625B155B477D6A526B58AF685596443ABFA192F0D35E23C64E9B9E5C333CBCE438BCEA4A52E5C85FA9C93B78C745F81FF89F998CF3B3D66B3C83AB6E67E33EE85CF885C5473E38053BD66B8B5D59F0535AF923C43C7FEA656C77DAF320BF2894CAF596533DE7B6A3D9A4A78E5FE022892BF77101A0000',
                'roomPrice' => '95.00',
                'roomType' => 'extra_room',
                'beginRoomDate' => '2020-09-02 00:00:00',
                'endRoomDate' => '2020-09-04 00:00:0',
            ],
            [
                'goldenId' => 'RESA-0011971749',
                'boxId' => '885212',
                'experienceId' => '948145',
                'experiencePrice' => '1349',
                'currency' => 'EUR',
                'voucher' => '502944097',
                'arrivalDate' => '2020-09-12 12:00:00',
                'endDate' => '2020-09-13 12:00:00',
                'additionalComment' => '',
                'customerData' => '0x1F8B080000000000040075905B0B82401085CF6FF1B942C8AE4F5DE8B15F1011A2EB05BC84BA9144FFBDB3E32A42850CEBECF9CEECCCBCE0E08E0A0A11523C996D1967942850F346415375306118A2E26D43CD474EADA7530448986796CCA87F734756CDC8169652D47CE699D5F3519D6060E7586103173BC4033F133DB7751A7128CE9148DFFD7B2E3C7A8D7FC9BF39D63C3B87716B920DE7692D7DC0E9A77A933C1CAA8E392D53963263453225D7510BBE6AC2B3AC4F25943DD7FC0C73C1559450BA4FF1901A2DF67F49B3D5825BD03CE3A19F88ECD476F5C607611A4FF8D0010000',
                'components' => '0x1F8B08000000000004005D8E510EC2300C43DF51AA7EB313213EDA75129B069BBA56DA918153E0A67CA14A69623B8EAF782A4F269C6A65A6A89BD43B46EE041E44B2F1894D8AC86AD32165902EA96655CF45687773ECD214731BC4BD5934F73BF94F197467E425BFD6574EFDB398EEBFEA0573DA0C6B29DAF528A4A51CF888EDF9764392A52A3F65F75B2C79D6BEE7C6173C12103AF4000000',
                'roomPrice' => null,
                'roomType' => null,
                'beginRoomDate' => null,
                'endRoomDate' => null,
            ],
            [
                'goldenId' => 'RESA-0000000000',
                'boxId' => '885212',
                'experienceId' => '948145',
                'experiencePrice' => '1349',
                'currency' => 'EUR',
                'voucher' => '502944097',
                'arrivalDate' => '2020-09-12 12:00:00',
                'endDate' => '2020-09-13 12:00:00',
                'additionalComment' => '',
                'customerData' => '0x1F8B080000000000040075905B0B82401085CF6FF1B942C8AE4F5DE8B15F1011A2EB05BC84BA9144FFBDB3E32A42850CEBECF9CEECCCBCE0E08E0A0A11523C996D1967942850F346415375306118A2E26D43CD474EADA7530448986796CCA87F734756CDC8169652D47CE699D5F3519D6060E7586103173BC4033F133DB7751A7128CE9148DFFD7B2E3C7A8D7FC9BF39D63C3B87716B920DE7692D7DC0E9A77A933C1CAA8E392D53963263453225D7510BBE6AC2B3AC4F25943DD7FC0C73C1559450BA4FF1901A2DF67F49B3D5825BD03CE3A19F88ECD476F5C607611A4FF8D0010000',
                'components' => '',
                'roomPrice' => null,
                'roomType' => null,
                'beginRoomDate' => null,
                'endRoomDate' => null,
            ],
            [
                'goldenId' => 'BONBEJBO190517000249ROYAL',
                'boxId' => '54302',
                'experienceId' => '54302',
                'experiencePrice' => '54302',
                'voucher' => '555444333',
                'currency' => 'EUR',
                'arrivalDate' => '2020-02-02 12:00:00',
                'endDate' => '2020-02-05 12:00:00',
                'additionalComment' => 'i would like a sea view, please',
                'customerData' => '0x1F8B08000000000004006D904B6BC2401485CF4F916EAB41939AAA2B0BDD74E1AEBB223298A88198943CA0A5F8DFFB6572190A95615EF77EF79C99FBA3077DAA51AE930A7D71DB30774422F6297388376AD5A992D31572645E54726E198E4C06E3ACA2E4F49F7FE534F1B99E6C6D6C0EE170288D72A6DAB1969E5EEB89F9ACADCE818D7444E16A1A239BF38F0BD12A383E2AA132F16BAA582B94629452AB1B347AF88EB77F5BCD9BDEEF660FFE9E05EDBF5CEFDF5BFBBF369005DC482DB4C46F8EE31AF7B95538F299EFF9D0BD81FCD03E74AEE29F3DFB397815A8CFCCF1A65F02A11B60B2010000',
                'components' => '0x1F8B0800000000000400ED58DB6EDB4610DD4F21FCD2168D554BB6E3D84FB59DB83090B445ECA60F4D105014256D2B8B2C29C97602FF7BCF9C995D91925C2441E2024540F0B69C9DDBCEE52CDFBB2DE7DD00D723D775FBAE87EBAE3B708F303275A9BB7239BF6DB997789A60C4BBDA25EE0CF70A2353F70ED7C45DB81946FA38728CCF402F1C0678AB5D8611EF4A521498A1FCCEC12DF948AE8F3026F4B91B61CE14234255E25BC1ABC7FBCCBD7673B78323C799E04B8D6F1EE329CF04CF535CE7BC961811F9EF7014781FB86F30AFC473865191B7007D82E70C4701B9134AA956A464E4535093DAFC16F4CB687582B739CE19F9791BBF7243CE9AD1B2C4F8072B17D459DE6BEA3E857E29A807D454C6FF06CF947356EDCEDC981CAFA86DB03F771D8C3DA71F32932C6BE43922EBE5377834E5936A382165493B3DBCA66BAA76AACD1535CAE9B5893D9551964A99B4E80BB3B0A09CCC56C2AF4584ACC7907E983668C407B97948F954E6E93975116F4FA8F99C2B587044B4087A0E30B6E0936AB3A0C5F22EFA0C316F443B3B16B1352DA9E89BDC22AA00AF99CD4A3147F494D8F7CC235DC501233D618494666D6291ECB962C1CB81B224C711D747792CC8D137FC58317BD47B212B3A96811A63A5AD917CAB997F7FE0D8620E887F44FB59C3168D678D9CD4FC53328B5266E51CCF1A55225325055E193D91B622E3045FC52379AC0C81BA6F92BD457E1A5775615E4D2CA76A5CB7DC1B1C61BEE79A55F8A235EABDD93B8835EBC25DBA6393D8AE66178C84915920BCB6DC1DE946316A2B771BF97A7AE72A7A6F0B6B257A97183942E6FDC053F80F684507E3578CA2196DBCC188AE85D08927F549ACDFE1D1757BEEB13BC4B5EBDE5A6C8C2C7A26989D5396DAF2B965EFA2F21FE2DCBF47F69F9835FA42B2BB94BDC7F361EC56A9BB90B74FCBDFD2EA67A09F35A2FA4B58BD2EB987F3847C24CA65C6C3C8DDA35CA929E307962CE753AB6153487B18A90738CF68E7E081243ED928F18D5599929535B3BA2F917E0E6F0C59E3B4E214ACB1C14B4FAD7BD756BF8E894772AB9529FBC08CFD6D8955E6ACF7DA4F1616D9439397C4CE97C6AEEC89940ACAECB367FAD84784E686D55BA4FF448FA4D02BE02F6F5845E47F1B114FCD4E515256404485FB2E229514E736E88EC921748F297509FD4351DBEA8C73EB73153B8C5829AB90B8DFCDC6750953AE556AF84A7AC115FD59F3FDD37456BB0BA24C41A913D6AE342299C23C1C505017738FC825698D3EB6D1551997C41075A32BD62B7D2B4850243B8AB845BBF8987536671DD5759C18B26D63F14BC35805DE36A3F0D4D0ABC8DBE698220D91FCC2F0A13CFF6C486A420CF01A34C736AF8F7B4DFEABFC7AC6F339B5CE29738F58AF872C52BD45D70BEBC2613FB28E7A747F20A86B4E5CE02D9A430E788ECD0CE9CD2372F286963B1105F499B97F59F69DD16FD72659F085A0BB39119FEA5751AEC4A3E4419F2B2BD48A4573A351BF4F59098EB0EA1D548A307B41AC7D0D2EA73177854A296AAEE535AC6EF3B88FFF29FD5FC77AD1FCF616476DA83EB9F7CB36EAD8FAD731E37668569786BFC4CB6DADB7B0768146ABE794B44B3C3530BAD5CAB909AD7DCADE33858481AD4BF0C22B8BD2E7E4DEA7EF6AC3DD1227123FBB4022CA2167BD903AA8B3BD55E81F376461BDA243E812C10763C3C2C166C15A09119F44FF3E477A6BD4CD2AB7CCD9346AF43DB54D8C9F726BF3FABA03FFBA03FFBA03FF2F77E09BF084587DCA5A5A114385FAB4DCB3CF6D5D963BD0F6CEF61C3D7B53A53C37EEA26DE86541FF90F99BB96D37AA469BE725232DA3654BAE62579F6BF121BCCF70DFC4BB1D5D8177C66CB9BD87466BA4D6DCD396ACFDD84B94EA1A5FFAB1E72BCD358FCE47D7F091ED43D5C6092D1E91F33C6AD0C5D1610D164CB707F4F2D85097C64E93760F7A76B02F79C2E3006FBBD1FA9C919B47D42FF453E28D49DC3DE4FC9F52F169403F6864C99EE205F3624CDFA8D5CD0EF47FFE2F3461948E58934771CD7DE43F636E857C157C7D439A21B3467C1EF0DF1CF127B65CC4FDDF2BD2CCE35A77702A6D4E7B746FE5497DFB01F3B4C60FF02533D4D444463D44C80E2223507AC685DA7388A3837367634EBD60B5D5BA9A5B9F6DAF66A84C63EB094FFF05257457E220BF672D0BEE649AB48FEEA15C76AAF4A3E75586566E58997322D85E7CAE5B31BEF94F5EC91CF9CBAC0F68F5F3FC05E8DA3F2D3D0491497E777195BF5C72958A20FFDB240A4A43F17716B98AA932E8DAAE6ABF192FB54A22F704369FACC4EDB2DEC99FAC8C11573BFD47BA4E59996D72CF9809C55A043EA1FE0790DE331D17CC1EC53595C5B5279F59C3C3F25C46EDC38ACDA22EC2E9CAEAD5D8EACEAE55CAF03FA259F1EE62C729A9ADA7DC5FAC7254B172690518D28779A3A2BDD99043A2674A2D967B9643DB6BE9EE513B4E16BBD033ACC3CB46874AF935EC7443CEFCCAB8F59C27D5FB36DA77E7FE01ACBCB9DB6C1B0000',
                'roomPrice' => '',
                'roomType' => '',
                'beginRoomDate' => '',
                'endRoomDate' => '',
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
