<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Support;

use Banelsems\LaraSgmefQr\Contracts\SleeperInterface;

/**
 * Sleeper de test qui n'attend pas réellement.
 *
 * Enregistre les appels pour permettre aux tests d'assertionner
 * le nombre et la durée des sleeps.
 */
final class FakeSleeper implements SleeperInterface
{
    /** @var list<int> */
    public array $sleeps = [];

    public function sleepMilliseconds(int $milliseconds): void
    {
        $this->sleeps[] = $milliseconds;
    }

    public function totalSleptMilliseconds(): int
    {
        return array_sum($this->sleeps);
    }

    public function reset(): void
    {
        $this->sleeps = [];
    }
}
