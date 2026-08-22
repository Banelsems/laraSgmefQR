<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Banelsems\LaraSgmefQr\Models\Invoice;
use InvalidArgumentException;
use RuntimeException;

/**
 * Génération et validation de QR codes e-MECeF.
 *
 * Utilise bacon/bacon-qr-code (pure PHP, léger) pour générer de vrais
 * QR codes lisibles par les scanners de la DGI.
 */
class QrCodeService
{
    /**
     * Valide que les données QR d'une facture contiennent l'UID ou le code MECeF.
     */
    public function validateInvoiceQr(Invoice $invoice): bool
    {
        if (!$invoice->uid || !$invoice->qr_code_data) {
            return false;
        }

        $payload = $invoice->qr_code_data;

        return str_contains($payload, $invoice->uid)
            || ($invoice->mecf_code && str_contains($payload, $invoice->mecf_code));
    }

    /**
     * Retourne l'URL de vérification DGI pour une facture confirmée.
     */
    public function verificationUrl(Invoice $invoice): string
    {
        if (!$invoice->qr_code_data) {
            throw new InvalidArgumentException('La facture ne contient pas de données QR.');
        }

        return config('lara_sgmef_qr.verification_url', 'https://developper.impots.bj/sygmef-test/verification')
            . '?q=' . urlencode($invoice->qr_code_data);
    }

    /**
     * Génère un vrai QR code au format SVG depuis un payload.
     *
     * @param  string  $payload  Données à encoder (généralement le qrCode de SecurityElementsDto).
     * @param  int  $size  Taille en pixels.
     */
    public function svg(string $payload, int $size = 300): string
    {
        return $this->generateSvg($payload, $size);
    }

    /**
     * Génère un vrai QR code au format PNG depuis un payload.
     *
     * Nécessite l'extension PHP ext-gd. Si elle n'est pas disponible,
     * une exception explicite est levée — png() ne retourne jamais
     * silencieusement du SVG.
     *
     * @param  string  $payload  Données à encoder.
     * @param  int  $size  Taille en pixels.
     *
     * @throws RuntimeException si ext-gd n'est pas disponible.
     */
    public function png(string $payload, int $size = 300): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException(
                'png() nécessite l\'extension PHP ext-gd. '
                . 'Utilisez svg() (format vectoriel, sans dépendance) '
                . 'ou installez ext-gd pour générer des PNG.'
            );
        }

        return $this->generatePng($payload, $size);
    }

    /**
     * Génère un QR code SVG via bacon/bacon-qr-code.
     *
     * Le format SVG est préféré car il est vectoriel, léger, et
     * n'a pas de dépendance ext-gd ou ext-imagick.
     */
    private function generateSvg(string $payload, int $size): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($payload, Encoder::DEFAULT_BYTE_MODE_ENCODING);
    }

    /**
     * Génère un QR code PNG via bacon/bacon-qr-code (GDLibRenderer).
     *
     * Nécessite ext-gd. Le format PNG produit commence par la signature
     * magique 89 50 4E 47 0D 0A 1A 0A.
     */
    private function generatePng(string $payload, int $size): string
    {
        $renderer = new GDLibRenderer($size);

        $writer = new Writer($renderer);

        return $writer->writeString($payload, Encoder::DEFAULT_BYTE_MODE_ENCODING);
    }
}
