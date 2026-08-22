<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Events;

use Banelsems\LaraSgmefQr\Exceptions\InvoiceException;
use Banelsems\LaraSgmefQr\Models\Invoice;

class InvoiceConfirmationFailed
{
    public function __construct(
        public readonly Invoice $invoice,
        public readonly InvoiceException $exception
    ) {
    }
}
