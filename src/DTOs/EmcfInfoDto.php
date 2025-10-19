<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Data;

class EmcfInfoDto extends Data
{
    public function __construct(
        public string $nim,
        public string $serial,
        public string $type
    ) {}
}
