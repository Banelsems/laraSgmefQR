<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Support;

use InvalidArgumentException;

/**
 * Value object immutable représentant un montant monétaire en XOF (Franc CFA).
 *
 * Le XOF n'a pas de subdivision (0 décimales), donc 1 unité = 1 XOF.
 * La représentation interne est un entier (int), ce qui garantit des
 * calculs déterministes sans erreurs d'arrondi liées aux floats.
 *
 * Toutes les opérations monétaires du package (DTOs, taxes, totaux, paiements)
 * doivent utiliser cette abstraction plutôt que des floats.
 */
final readonly class Money
{
    public function __construct(
        private int $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException(
                'Money ne peut pas être négatif. Valeur reçue : ' . $amount . ' XOF.'
            );
        }
    }

    /**
     * Crée une instance de Money depuis une valeur acceptant int, float ou string.
     *
     * Les floats sont arrondis à l'entier le plus proche (XOF = 0 décimales).
     */
    public static function from(int|float|string $value): self
    {
        if (is_string($value)) {
            $value = (float) $value;
        }

        if (is_float($value)) {
            $value = (int) round($value);
        }

        return new self($value);
    }

    /**
     * Crée une instance de Money depuis des centimes (minor units).
     *
     * Pour XOF (0 décimales), 1 minor unit = 1 XOF, donc cette méthode
     * est équivalente à from() mais sémantiquement explicite pour les
     * consommateurs qui pensent en centimes.
     */
    public static function fromMinorUnits(int $minorUnits): self
    {
        return new self($minorUnits);
    }

    /**
     * Retourne le montant en XOF (entier).
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Retourne le montant en centimes (minor units).
     *
     * Pour XOF (0 décimales), équivalent à getAmount().
     */
    public function getMinorUnits(): int
    {
        return $this->amount;
    }

    /**
     * Retourne le montant formaté pour l'API e-MECeF.
     *
     * L'API e-MECeF accepte les montants comme nombres (float ou int).
     * Pour XOF sans décimales, on retourne un entier.
     */
    public function toApiPayload(): int
    {
        return $this->amount;
    }

    /**
     * Addition de deux montants.
     */
    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    /**
     * Soustraction de deux montants. Lève une exception si le résultat est négatif.
     */
    public function subtract(self $other): self
    {
        return new self($this->amount - $other->amount);
    }

    /**
     * Multiplication par une quantité (entière ou décimale).
     *
     * Le résultat est arrondi à l'entier le plus proche.
     */
    public function multiply(int|float $factor): self
    {
        if (is_float($factor)) {
            return new self((int) round($this->amount * $factor));
        }

        return new self($this->amount * $factor);
    }

    /**
     * Comparaison d'égalité.
     */
    public function equals(self $other): bool
    {
        return $this->amount === $other->amount;
    }

    /**
     * Vérifie si ce montant est supérieur à un autre.
     */
    public function isGreaterThan(self $other): bool
    {
        return $this->amount > $other->amount;
    }

    /**
     * Vérifie si ce montant est supérieur ou égal à un autre.
     */
    public function isGreaterThanOrEqualTo(self $other): bool
    {
        return $this->amount >= $other->amount;
    }

    /**
     * Vérifie si ce montant est inférieur à un autre.
     */
    public function isLessThan(self $other): bool
    {
        return $this->amount < $other->amount;
    }

    /**
     * Vérifie si ce montant est nul (zéro).
     */
    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    /**
     * Représentation string pour debug et logs.
     */
    public function __toString(): string
    {
        return $this->amount . ' XOF';
    }
}
