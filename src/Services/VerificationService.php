<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\DTOs\VerificationResultDto;
use Banelsems\LaraSgmefQr\Models\Invoice;

/**
 * Service de vérification post-confirmation des factures e-MECeF.
 *
 * Valide l'intégrité des éléments de sécurité obligatoires après confirmation :
 * - Présence du QR code et cohérence avec l'UID / code MECeF
 * - Présence du code MECeF/DGI
 * - Présence de la date/heure e-MECeF
 * - Présence des compteurs
 * - Présence du NIM
 *
 * Génère également l'URL de vérification sur le portail DGI.
 */
class VerificationService
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {
    }

    /**
     * Vérifie une facture confirmée et retourne un résultat structuré.
     */
    public function verify(Invoice $invoice): VerificationResultDto
    {
        $checks = [];
        $issues = [];

        // Vérification : la facture doit être confirmée
        $checks['invoice_confirmed'] = $invoice->isConfirmed();
        if (!$checks['invoice_confirmed']) {
            $issues[] = 'La facture n\'est pas confirmée.';
        }

        // Vérification : UID présent
        $checks['uid_present'] = filled($invoice->uid);
        if (!$checks['uid_present']) {
            $issues[] = 'UID e-MECeF manquant.';
        }

        // Vérification : QR code présent
        $checks['qr_code_present'] = $invoice->hasQrCode();
        if (!$checks['qr_code_present']) {
            $issues[] = 'QR code manquant.';
        }

        // Vérification : QR code valide (contient l'UID ou le code MECeF)
        $checks['qr_code_valid'] = $invoice->hasQrCode() && $this->qrCodeService->validateInvoiceQr($invoice);
        if (!$checks['qr_code_valid'] && $checks['qr_code_present']) {
            $issues[] = 'Le QR code ne contient pas l\'UID ni le code MECeF.';
        }

        // Vérification : code MECeF/DGI présent
        $checks['mecf_code_present'] = filled($invoice->mecfCode());
        if (!$checks['mecf_code_present']) {
            $issues[] = 'Code MECeF/DGI manquant.';
        }

        // Vérification : date/heure e-MECeF présente
        $checks['mecf_datetime_present'] = filled($invoice->mecfDateTime());
        if (!$checks['mecf_datetime_present']) {
            $issues[] = 'Date/heure e-MECeF manquante.';
        }

        // Vérification : compteurs présents
        $checks['counters_present'] = filled($invoice->counters());
        if (!$checks['counters_present']) {
            $issues[] = 'Compteurs e-MECeF manquants.';
        }

        // Vérification : NIM présent
        $checks['nim_present'] = filled($invoice->nim());
        if (!$checks['nim_present']) {
            $issues[] = 'NIM (Numéro d\'Identification du Matériel) manquant.';
        }

        // URL de vérification DGI (si QR code présent)
        $verificationUrl = null;
        if ($invoice->hasQrCode()) {
            try {
                $verificationUrl = $this->qrCodeService->verificationUrl($invoice);
            } catch (\InvalidArgumentException) {
                // QR code présent mais données invalides — l'URL ne peut pas être générée
            }
        }

        $valid = !in_array(false, $checks, true);

        return new VerificationResultDto(
            valid: $valid,
            verificationUrl: $verificationUrl,
            checks: $checks,
            issues: $issues,
        );
    }
}
