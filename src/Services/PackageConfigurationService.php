<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\ConfigWriterInterface;
use Illuminate\Support\Facades\Log;

/**
 * Service de configuration du package LaraSgmefQR.
 *
 * Responsabilités :
 * - Lire la configuration courante pour affichage
 * - Mettre à jour la configuration via ConfigWriterInterface
 *
 * @deprecated L'écriture runtime du fichier de configuration est déconseillée
 *             depuis v4.0.0. Préférez les variables d'environnement (.env).
 */
class PackageConfigurationService
{
    public function __construct(
        private readonly ConfigWriterInterface $configWriter
    ) {
    }

    /**
     * Retourne la configuration courante pour affichage.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return [
            'api_url' => config('lara_sgmef_qr.api_url'),
            'token' => config('lara_sgmef_qr.token') ? '***' : null,
            'default_ifu' => config('lara_sgmef_qr.default_ifu'),
            'default_operator_name' => config('lara_sgmef_qr.default_operator_name'),
            'http_timeout' => config('lara_sgmef_qr.http_options.timeout'),
            'verify_ssl' => config('lara_sgmef_qr.http_options.verify'),
            'logging_enabled' => config('lara_sgmef_qr.logging.enabled'),
            'web_interface_enabled' => config('lara_sgmef_qr.web_interface.enabled'),
        ];
    }

    /**
     * Met à jour la configuration depuis les données validées du formulaire.
     *
     * @param  array<string, mixed>  $validatedData
     * @param  string  $configPath  Chemin du fichier de configuration.
     */
    public function update(array $validatedData, string $configPath): void
    {
        $changes = $this->mapFormToConfigKeys($validatedData);

        if (count($changes) === 0) {
            return;
        }

        Log::info('PackageConfigurationService: updating config', [
            'keys' => array_keys($changes),
        ]);

        $this->configWriter->write($configPath, $changes);
    }

    /**
     * Mappe les champs du formulaire vers les clés de configuration.
     *
     * @param  array<string, mixed>  $validatedData
     * @return array<string, mixed>
     */
    private function mapFormToConfigKeys(array $validatedData): array
    {
        $changes = [];

        if (isset($validatedData['api_url'])) {
            $changes['api_url'] = $validatedData['api_url'];
        }

        if (isset($validatedData['token']) && $validatedData['token'] !== '***') {
            $changes['token'] = $validatedData['token'];
        }

        if (isset($validatedData['default_ifu'])) {
            $changes['default_ifu'] = $validatedData['default_ifu'];
        }

        if (isset($validatedData['default_operator_name'])) {
            $changes['default_operator_name'] = $validatedData['default_operator_name'];
        }

        if (isset($validatedData['http_timeout'])) {
            $changes['http_options.timeout'] = (int) $validatedData['http_timeout'];
        }

        if (isset($validatedData['verify_ssl'])) {
            $changes['http_options.verify'] = (bool) $validatedData['verify_ssl'];
        }

        if (isset($validatedData['logging_enabled'])) {
            $changes['logging.enabled'] = (bool) $validatedData['logging_enabled'];
        }

        if (isset($validatedData['web_interface_enabled'])) {
            $changes['web_interface.enabled'] = (bool) $validatedData['web_interface_enabled'];
        }

        return $changes;
    }
}
