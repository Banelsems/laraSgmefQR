<?php

namespace Banelsems\LaraSgmefQr\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CheckConfigCommand extends Command
{
    protected $signature = 'sgmef:check-config';
    protected $description = 'Vérifie la configuration du package LaraSgmefQR';

    public function handle()
    {
        $this->info('Vérification de la configuration de LaraSgmefQR...');

        $requiredKeys = [
            'lara_sgmef_qr.api.url',
            'lara_sgmef_qr.api.token',
            'lara_sgmef_qr.company_info.ifu',
        ];

        $allGood = true;

        foreach ($requiredKeys as $key) {
            if (!Config::has($key) || Config::get($key) === null) {
                $this->error("Clé de configuration manquante ou vide : {$key}");
                $allGood = false;
            }
        }

        if ($allGood) {
            $this->info('Configuration validée avec succès !');
        } else {
            $this->warn('Veuillez vérifier votre fichier .env et la configuration du package.');
        }

        return $allGood ? 0 : 1;
    }
}
