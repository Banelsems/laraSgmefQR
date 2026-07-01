<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class InvoiceResponseDto extends Data
{
    public function __construct(
        public readonly ?string $uid = null,
        #[MapInputName('total')]
        public readonly ?float $totalAmount = null,
        #[MapInputName('ts')]
        public readonly ?float $totalTaxAmount = null,
        #[MapInputName('aib')]
        public readonly ?float $totalAibAmount = null,
        public readonly ?array $items = null,
        public readonly ?string $status = null,
        #[MapInputName('date')]
        public readonly ?string $dateTime = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorDesc = null,
        public readonly ?int $ta = null,
        public readonly ?int $tb = null,
        public readonly ?int $tc = null,
        public readonly ?int $td = null,
        public readonly ?int $taa = null,
        public readonly ?int $tab = null,
        public readonly ?int $tac = null,
        public readonly ?int $tad = null,
        public readonly ?int $tae = null,
        public readonly ?int $taf = null,
        public readonly ?int $hab = null,
        public readonly ?int $had = null,
        public readonly ?int $vab = null,
        public readonly ?int $vad = null
    ) {
    }

    public function hasError(): bool
    {
        return filled($this->errorCode) && strtoupper((string) $this->errorCode) !== 'OK';
    }
}
