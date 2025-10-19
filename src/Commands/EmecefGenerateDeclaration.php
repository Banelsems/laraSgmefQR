<?php

namespace Banelsems\LaraSgmefQr\Commands;

use Banelsems\LaraSgmefQr\Services\DeclarationGeneratorService;
use Illuminate\Console\Command;

class EmecefGenerateDeclaration extends Command
{
    protected $signature = 'emecef:generate-declaration';
    protected $description = 'Génère le dossier d\'auto-déclaration e-MECeF complet (Annexe 1 & 2).';

    public function handle(DeclarationGeneratorService $generator)
    {
        $this->info('Début de la génération du dossier d\'auto-déclaration...');

        try {
            $testCases = config('lara_sgmef_qr.emecef_test_cases');
            $this->info(count($testCases) . ' cas de test à traiter.');

            // Note: La barre de progression est gérée à l'intérieur du service pour plus de précision
            // mais pour cet exemple, nous l'appelons directement ici.
            $pdfPath = $this->withProgressBar(range(1, count($testCases)), function () use ($generator) {
                return $generator->generateDeclaration();
            });

            $this->info("\nLe dossier de déclaration a été généré avec succès.");
            $this->line("Fichier PDF disponible à l'emplacement : <comment>{$pdfPath}</comment>");

        } catch (\Exception $e) {
            $this->error("\nUne erreur est survenue lors de la génération :");
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
