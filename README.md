# LaraSgmefQR

LaraSgmefQR is a Laravel package that provides a way to normalize invoice data from the API. It is designed for integrating with the SGMEF API in Benin and generating QR codes in Laravel.

## Installation

To install LaraSgmefQR, you can run the following command:

```
composer require banelsems/lara-sgmef-qr
```

## Usage

To use LaraSgmefQR, you can first get the `InvoiceNormalize` class from the service container:

```php
$invoiceNormalize = app('laraSgmefQR');
```

Once you have the `InvoiceNormalize` class, you can use it to normalize the invoice data from the API:

```php
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
```

The `normalize` method returns a normalized invoice object. The normalized invoice object contains the following properties:

- `dateTime`: The date and time of the invoice.
- `qrCode`: The QR code of the invoice.
- `codeMECeFDGI`: The code of the invoice.
- `counters`: The counters of the invoice.
- `nim`: The nim of the invoice.
- `errorCode`: The error code of the invoice.
- `errorDesc`: The error description of the invoice.

## Testing

To run the tests for LaraSgmefQR, you can run the following command:

```
composer test
```

## Contributing

Contributions to LaraSgmefQR are welcome. Please open a pull request on GitHub if you have any changes or improvements.

## License

LaraSgmefQR is licensed under the MIT License.