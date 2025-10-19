<?php

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ApiStatusDto extends Data
{
    public function __construct(
        public string $status,
        public string $ifu,
        #[DataCollectionOf(EmcfInfoDto::class)]
        public DataCollection $emcf_info
    ) {}
}
