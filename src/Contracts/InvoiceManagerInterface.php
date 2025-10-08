<?php

namespace Banelsems\LaraSgmefQr\Contracts;

use Banelsems\LaraSgmefQr\DTOs\InvoiceRequestDto;
use Banelsems\LaraSgmefQr\Models\Invoice;

interface InvoiceManagerInterface
{
    public function createInvoice(InvoiceRequestDto $invoiceData): Invoice;
    public function confirmInvoice(string $uid): Invoice;
    public function cancelInvoice(string $uid): Invoice;
    public function getInvoice(string $uid): Invoice;
}
