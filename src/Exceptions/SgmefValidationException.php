<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur de validation de la requête par l'API (400 Bad Request, 422 Unprocessable Entity).
 *
 * Les données envoyées ne respectent pas les règles métier de l'API e-MECeF.
 */
class SgmefValidationException extends SgmefApiException
{
}
