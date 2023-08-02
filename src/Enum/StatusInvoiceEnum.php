<?php
namespace banelsems\LaraSgmefQR\src\Enum;


enum StatusInvoiceEnum:string
{
    case create  = 'create';
    case confirm = 'confirm';
    case cancel  = 'cancel';
    case pending = 'pending';
    case error   = 'error';
}