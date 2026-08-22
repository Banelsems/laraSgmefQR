<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur d'authentification (401 Unauthorized, 403 Forbidden).
 *
 * Le token est absent, expiré, invalide ou les permissions sont insuffisantes.
 */
class SgmefAuthenticationException extends SgmefApiException
{
}
