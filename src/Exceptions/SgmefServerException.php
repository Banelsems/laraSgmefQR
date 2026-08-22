<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur serveur (5xx).
 *
 * Le serveur e-MECeF a rencontré une erreur interne. Peut être
 * retryable selon l'opération (cf. SgmefRetryPolicy).
 */
class SgmefServerException extends SgmefApiException
{
}
