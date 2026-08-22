<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

/**
 * Résultat structuré de la vérification post-confirmation d'une facture.
 *
 * Contient le statut global, l'URL de vérification DGI, les vérifications
 * individuelles et la liste des problèmes détectés.
 */
class VerificationResultDto
{
    /**
     * @param  array<string, bool>  $checks  Vérifications individuelles (nom => résultat).
     * @param  list<string>  $issues  Problèmes détectés (vide si tout est valide).
     */
    public function __construct(
        public readonly bool $valid,
        public readonly ?string $verificationUrl = null,
        public readonly array $checks = [],
        public readonly array $issues = [],
    ) {
    }

    /**
     * Vérifie si une vérification individuelle a réussi.
     */
    public function checkPassed(string $name): bool
    {
        return $this->checks[$name] ?? false;
    }
}
