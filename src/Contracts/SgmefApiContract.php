<?php

namespace App\Contracts;

interface SgmefApiContract
{
    public function getStatuses();

    public function getTaxGroups();

    public function getInvoiceTypes();

    public function getPaymentTypes();

    public function createInvoice(array $data);

    public function getInvoice($uid);

    public function confirmInvoice($uid);
}