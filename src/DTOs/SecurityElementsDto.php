<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Data;

class SecurityElementsDto extends Data
{
    public function __construct(
        public readonly ?string $dateTime = null,
        public readonly ?string $qrCode = null,
        public readonly ?string $codeMECeFDGI = null,
        public readonly ?string $counters = null,
        public readonly ?string $nim = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorDesc = null,
        public readonly ?bool $refundWithAibPayment = null
    ) {
    }

    public function hasError(): bool
    {
        return filled($this->errorCode) && strtoupper((string) $this->errorCode) !== 'OK';
    }
}
