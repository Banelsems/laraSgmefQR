<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

/**
 * Abstraction légère pour sleep() afin de tester les retry policies
 * sans réellement attendre.
 */
interface SleeperInterface
{
    /**
     * Endort le processus pendant le nombre de millisecondes indiqué.
     */
    public function sleepMilliseconds(int $milliseconds): void;
}
