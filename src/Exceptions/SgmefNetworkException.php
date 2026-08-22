<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur réseau (connexion impossible, DNS, etc.).
 *
 * Le serveur e-MECeF n'a pas pu être joint. Peut être retryable
 * selon l'opération (cf. SgmefRetryPolicy).
 */
class SgmefNetworkException extends SgmefApiException
{
}
