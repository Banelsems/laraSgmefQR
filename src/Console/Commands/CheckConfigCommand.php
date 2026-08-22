<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CheckConfigCommand extends Command
{
    /**
     * Required configuration keys with their human-readable label.
     *
     * Keys are the actual config paths used by the package (see config/lara_sgmef_qr.php).
     *
     * @var array<string, string>
     */
    private const REQUIRED_KEYS = [
        'lara_sgmef_qr.api_url' => 'URL de l\'API e-MECeF',
        'lara_sgmef_qr.token' => 'Token JWT e-MECeF',
        'lara_sgmef_qr.default_ifu' => 'IFU par défaut',
    ];

    protected $signature = 'sgmef:check-config';

    protected $description = 'Vérifie la configuration du package LaraSgmefQR';

    public function handle(): int
    {
        $this->info('Vérification de la configuration de LaraSgmefQR...');

        $allGood = true;

        foreach (self::REQUIRED_KEYS as $key => $label) {
            if (!Config::has($key) || $this->isEmptyConfigValue(Config::get($key))) {
                $this->error("{$label} manquant ou vide (clé : {$key}).");
                $allGood = false;
            }
        }

        if ($allGood) {
            $this->info('Configuration validée avec succès !');

            return self::SUCCESS;
        }

        $this->warn('Veuillez vérifier votre fichier .env et la configuration du package.');

        return self::FAILURE;
    }

    /**
     * A config value is considered empty when it is null, an empty string,
     * or a string containing only whitespace.
     */
    private function isEmptyConfigValue(mixed $value): bool
    {
        return $value === null
            || ($value === '')
            || (is_string($value) && trim($value) === '');
    }
}
