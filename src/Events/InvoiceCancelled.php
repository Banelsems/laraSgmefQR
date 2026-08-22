<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Events;

use Banelsems\LaraSgmefQr\Models\Invoice;

class InvoiceCancelled
{
    public function __construct(
        public readonly Invoice $invoice
    ) {
    }
}
