<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Exceptions;

/**
 * Erreur de ressource introuvable (404 Not Found).
 *
 * La facture ou la ressource demandée n'existe pas côté e-MECeF.
 */
class SgmefNotFoundException extends SgmefApiException
{
}
