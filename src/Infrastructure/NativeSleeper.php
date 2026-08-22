<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

use Banelsems\LaraSgmefQr\Contracts\SleeperInterface;

/**
 * Implémentation réelle de SleeperInterface utilisant usleep().
 */
final class NativeSleeper implements SleeperInterface
{
    public function sleepMilliseconds(int $milliseconds): void
    {
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }
}
