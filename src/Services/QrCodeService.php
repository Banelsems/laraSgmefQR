<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Models\Invoice;
use InvalidArgumentException;

class QrCodeService
{
    public function validateInvoiceQr(Invoice $invoice): bool
    {
        if (!$invoice->uid || !$invoice->qr_code_data) {
            return false;
        }

        $payload = $invoice->qr_code_data;

        return str_contains($payload, $invoice->uid)
            || ($invoice->mecf_code && str_contains($payload, $invoice->mecf_code));
    }

    public function verificationUrl(Invoice $invoice): string
    {
        if (!$invoice->qr_code_data) {
            throw new InvalidArgumentException('La facture ne contient pas de données QR.');
        }

        return config('lara_sgmef_qr.verification_url', 'https://developper.impots.bj/sygmef-test/verification')
            . '?q=' . urlencode($invoice->qr_code_data);
    }

    public function svg(string $payload, int $size = 300): string
    {
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}" role="img" aria-label="QR e-MECeF">
              <rect width="100%" height="100%" fill="#fff"/>
              <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="monospace" font-size="10" fill="#111">{$escaped}</text>
            </svg>
            SVG;
    }
}
