<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

interface IfuResolverInterface
{
    public function resolve(?string $context = null): string;
}
