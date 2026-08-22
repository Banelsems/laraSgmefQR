<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

/**
 * Écriture de configuration du package.
 *
 * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
 *             L'écriture runtime du fichier de configuration est
 *             déconseillée : préférez les variables d'environnement (.env).
 *             Cette interface est conservée pour compatibilité mais
 *             sera retirée dans la prochaine version majeure.
 */
interface ConfigWriterInterface
{
    /**
     * Met à jour les clés spécifiées dans le fichier de configuration.
     *
     * @param  string  $path  Chemin absolu du fichier de configuration.
     * @param  array<string, mixed>  $changes  Clés à modifier (notation par points pour les clés imbriquées).
     */
    public function write(string $path, array $changes): void;
}
