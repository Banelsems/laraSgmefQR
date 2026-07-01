<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\IfuResolverInterface;
use InvalidArgumentException;

class DefaultIfuResolver implements IfuResolverInterface
{
    public function resolve(?string $context = null): string
    {
        $ifu = (string) config('lara_sgmef_qr.default_ifu', config('lara_sgmef_qr.company_info.ifu'));

        if (!preg_match('/^\d{13}$/', $ifu)) {
            throw new InvalidArgumentException('Aucun IFU valide par défaut n’est configuré pour LaraSgmefQR.');
        }

        return $ifu;
    }
}
