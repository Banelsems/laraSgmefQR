<?php

namespace banelsems\LaraSgmefQR\src\Contracts;

interface SgmefApiContract
{
    public function getInvoiceStatuses();

    public function getInvoiceTypeList();

    public function getPaymentTypeList();

    public function createInvoice(array $data);

    public function getInvoice(string $uid);

    public function confirmInvoice(string $uid);

    public function cancelInvoice(string $uid);

    public function getSupportedCountries();

    public function getInvoiceByUid(string $uid): array;
}