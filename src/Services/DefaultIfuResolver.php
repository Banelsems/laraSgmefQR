<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;
use InvalidArgumentException;

/**
 * Résolveur d'IFU supportant le multi-IFU.
 *
 * Stratégie de résolution :
 * 1. Si un contexte est fourni et existe dans `ifu_map`, retourne l'IFU mappé.
 * 2. Sinon, si `ifus` est configuré (liste), retourne le premier IFU valide.
 * 3. Sinon, utilise `default_ifu` (rétrocompatibilité).
 *
 * Configuration dans config/lara_sgmef_qr.php :
 *   'default_ifu' => env('SGMEF_DEFAULT_IFU'),
 *   'ifus' => ['1234567890123', '9876543210987'],
 *   'ifu_map' => [
 *       'agency_a' => '1234567890123',
 *       'agency_b' => '9876543210987',
 *   ],
 */
class DefaultIfuResolver implements IfuResolverInterface
{
    public function resolve(?string $context = null): string
    {
        // 1. Résolution par contexte via ifu_map
        if ($context !== null && $context !== '') {
            $ifuMap = config('lara_sgmef_qr.ifu_map', []);
            if (is_array($ifuMap) && isset($ifuMap[$context])) {
                $ifu = (string) $ifuMap[$context];
                if ($this->isValid($ifu)) {
                    return $ifu;
                }
            }
        }

        // 2. Premier IFU valide depuis la liste ifus
        $ifus = config('lara_sgmef_qr.ifus', []);
        if (is_array($ifus)) {
            foreach ($ifus as $ifu) {
                $ifu = (string) $ifu;
                if ($this->isValid($ifu)) {
                    return $ifu;
                }
            }
        }

        // 3. Fallback : default_ifu
        $ifu = (string) config('lara_sgmef_qr.default_ifu', config('lara_sgmef_qr.company_info.ifu'));

        if (!$this->isValid($ifu)) {
            throw new InvalidArgumentException('Aucun IFU valide par défaut n\'est configuré pour LaraSgmefQR.');
        }

        return $ifu;
    }

    /**
     * Retourne tous les IFUs configurés.
     *
     * @return string[]
     */
    public function all(): array
    {
        $ifus = array_filter(
            array_map(fn ($ifu) => $this->isValid((string) $ifu) ? (string) $ifu : null, config('lara_sgmef_qr.ifus', []) ?: []),
            fn ($ifu) => $ifu !== null
        );

        $default = (string) config('lara_sgmef_qr.default_ifu', config('lara_sgmef_qr.company_info.ifu'));
        if ($this->isValid($default) && !in_array($default, $ifus, true)) {
            $ifus[] = $default;
        }

        return $ifus;
    }

    private function isValid(string $ifu): bool
    {
        return preg_match('/^\d{13}$/', $ifu) === 1;
    }
}
