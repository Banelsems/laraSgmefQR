<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Events;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Exceptions\SgmefApiException;

class InvoiceCreationFailed
{
    public function __construct(
        public readonly InvoiceRequestDto $invoiceData,
        public readonly SgmefApiException $exception
    ) {
    }
}
