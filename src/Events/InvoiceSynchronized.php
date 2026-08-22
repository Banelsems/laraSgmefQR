<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Events;

use Banelsems\LaraSgmefQr\Models\Invoice;

class InvoiceSynchronized
{
    public function __construct(
        public readonly Invoice $invoice
    ) {
    }
}
