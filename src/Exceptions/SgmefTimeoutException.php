<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur de timeout (délai dépassé pour la requête HTTP).
 *
 * La connexion ou la lecture a dépassé le timeout configuré.
 * Peut être retryable selon l'opération (cf. SgmefRetryPolicy).
 */
class SgmefTimeoutException extends SgmefApiException
{
}
