<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\DTOs;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ApiStatusDto extends Data
{
    public function __construct(
        public bool|string $status,
        public ?string $ifu = null,
        public ?string $version = null,
        public ?string $nim = null,
        public ?string $tokenValid = null,
        public ?string $serverDateTime = null,
        #[DataCollectionOf(EmcfInfoDto::class)]
        #[MapInputName('emcfList')]
        public ?DataCollection $emcfInfo = null
    ) {
    }
}
