<?php

namespace PHPSTORM_META;

// Fichier pour améliorer l'autocomplétion de l'IDE

override(
    app(0),
    map([
        'sgmef.api' => \Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class,
        'sgmef.invoices' => \Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface::class,
    ])
);

override(
    resolve(0),
    map([
        'sgmef.api' => \Banelsems\LaraSgmefQr\Contracts\SgmefApiClientInterface::class,
        'sgmef.invoices' => \Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface::class,
    ])
);
