<?php
use App\Providers\LaraSgmefQRServiceProvider;

class LaraSgmefQRTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(LaraSgmefQRServiceProvider::class);
    }

    public function testInvoiceNormalize()
    {
        $invoiceNormalize = $this->app->make('laraSgmefQR')->getInvoiceNormalize();

        $data = [
            'dateTime' => '2023-07-02T15:22:34+00:00',
            'qrCode' => '1234567890',
            'codeMECeFDGI' => '1234567890',
            'counters' => '1234567890',
            'nim' => '1234567890',
            'errorCode' => 'OK',
            'errorDesc' => 'No error',
        ];

        $normalizedInvoice = $invoiceNormalize->normalize($data);

        $this->assertEquals('2023-07-02 15:22:34', $normalizedInvoice['dateTime']);
        $this->assertEquals('1234567890', $normalizedInvoice['qrCode']);
        $this->assertEquals('1234567890', $normalizedInvoice['codeMECeFDGI']);
        $this->assertEquals('1234567890', $normalizedInvoice['counters']);
        $this->assertEquals('1234567890', $normalizedInvoice['nim']);
        $this->assertEquals('OK', $normalizedInvoice['errorCode']);
        $this->assertEquals('No error', $normalizedInvoice['errorDesc']);
    }
}
