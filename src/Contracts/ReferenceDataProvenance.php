<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Contracts;

/**
 * Provenance des données de référentiel.
 */
enum ReferenceDataProvenance: string
{
    case API = 'api';
    case CACHE = 'cache';
    case FALLBACK = 'fallback';
}
