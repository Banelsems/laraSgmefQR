<?php

return [
    [
        'name' => 'Cas 1: FV groupe A',
        'description' => 'Facture de vente avec un seul article exonéré.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article exonéré', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'A']], 'payment' => [['name' => 'ESPECES', 'amount' => 1000]]],
    ],
    [
        'name' => 'Cas 2: FV groupe B',
        'description' => 'Facture de vente avec TVA 18%.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article TVA', 'price' => 2000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'ESPECES', 'amount' => 2000]]],
    ],
    [
        'name' => 'Cas 3: FV groupe C',
        'description' => 'Facture TVA 18% + AIB 1%.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Service AIB A', 'price' => 3000, 'quantity' => 1, 'taxGroup' => 'C']], 'payment' => [['name' => 'VIREMENT', 'amount' => 3000]]],
    ],
    [
        'name' => 'Cas 4: FV groupe D',
        'description' => 'Facture TVA 18% + AIB 5%.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Service AIB B', 'price' => 4000, 'quantity' => 1, 'taxGroup' => 'D']], 'payment' => [['name' => 'VIREMENT', 'amount' => 4000]]],
    ],
    [
        'name' => 'Cas 5: FV groupes B+C',
        'description' => 'Facture multi-articles avec groupes mixtes.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article B', 'price' => 1500, 'quantity' => 1, 'taxGroup' => 'B'], ['name' => 'Article C', 'price' => 2500, 'quantity' => 1, 'taxGroup' => 'C']], 'payment' => [['name' => 'ESPECES', 'amount' => 4000]]],
    ],
    [
        'name' => 'Cas 6: FV groupes A+F',
        'description' => 'Facture exonérée et exportation.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article exonéré', 'price' => 800, 'quantity' => 1, 'taxGroup' => 'A'], ['name' => 'Article export', 'price' => 1200, 'quantity' => 1, 'taxGroup' => 'F']], 'payment' => [['name' => 'ESPECES', 'amount' => 2000]]],
    ],
    [
        'name' => 'Cas 7: FA groupe B',
        'description' => 'Facture d’avoir avec référence.',
        'request' => ['type' => 'FA', 'reference' => 'ORIGINAL-FV-UID-001', 'items' => [['name' => 'Avoir TVA', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'ESPECES', 'amount' => 1000]]],
    ],
    [
        'name' => 'Cas 8: FA groupe C',
        'description' => 'Facture d’avoir avec AIB.',
        'request' => ['type' => 'FA', 'reference' => 'ORIGINAL-FV-UID-002', 'items' => [['name' => 'Avoir AIB', 'price' => 1100, 'quantity' => 1, 'taxGroup' => 'C']], 'payment' => [['name' => 'VIREMENT', 'amount' => 1100]]],
    ],
    [
        'name' => 'Cas 9: EV groupe B',
        'description' => 'Export de facture de vente.',
        'request' => ['type' => 'EV', 'items' => [['name' => 'Export vente', 'price' => 5000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'VIREMENT', 'amount' => 5000]]],
    ],
    [
        'name' => 'Cas 10: EA groupe B',
        'description' => 'Export de facture d’avoir.',
        'request' => ['type' => 'EA', 'reference' => 'ORIGINAL-EV-UID-001', 'items' => [['name' => 'Export avoir', 'price' => 900, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'VIREMENT', 'amount' => 900]]],
    ],
    [
        'name' => 'Cas 11: Paiement multiple',
        'description' => 'ESPECES + CARTEBANCAIRE.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article multi-paiement', 'price' => 6000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'ESPECES', 'amount' => 3000], ['name' => 'CARTEBANCAIRE', 'amount' => 3000]]],
    ],
    [
        'name' => 'Cas 12: Mobile money',
        'description' => 'Paiement MOBILEMONEY.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article mobile money', 'price' => 7000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'MOBILEMONEY', 'amount' => 7000]]],
    ],
    [
        'name' => 'Cas 13: Taxe spécifique',
        'description' => 'Article avec taxSpecific.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article taxe spécifique', 'price' => 8000, 'quantity' => 1, 'taxGroup' => 'B', 'taxSpecific' => 250]], 'payment' => [['name' => 'ESPECES', 'amount' => 8000]]],
    ],
    [
        'name' => 'Cas 14: Modification prix',
        'description' => 'Article avec originalPrice et priceModification.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article remisé', 'price' => 9000, 'quantity' => 1, 'taxGroup' => 'B', 'originalPrice' => 10000, 'priceModification' => 'REMISE']], 'payment' => [['name' => 'ESPECES', 'amount' => 9000]]],
    ],
    [
        'name' => 'Cas 15: Paiement VIREMENT+CHEQUES+CREDIT',
        'description' => 'Multi-paiement bancaire et crédit.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article crédit', 'price' => 15000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'VIREMENT', 'amount' => 5000], ['name' => 'CHEQUES', 'amount' => 5000], ['name' => 'CREDIT', 'amount' => 5000]]],
    ],
    [
        'name' => 'Cas 16: Service',
        'description' => 'Facture de prestation de service.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Prestation de service', 'price' => 20000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'VIREMENT', 'amount' => 20000]]],
    ],
    [
        'name' => 'Cas 17: Quantité décimale',
        'description' => 'Facture avec quantité fractionnée.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Produit au poids', 'price' => 1200, 'quantity' => 2, 'taxGroup' => 'B']], 'payment' => [['name' => 'ESPECES', 'amount' => 2400]]],
    ],
    [
        'name' => 'Cas 18: Client IFU',
        'description' => 'Facture avec IFU client.',
        'request' => ['type' => 'FV', 'client' => ['name' => 'Client IFU', 'ifu' => '1234567890123'], 'items' => [['name' => 'Article client IFU', 'price' => 3000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'ESPECES', 'amount' => 3000]]],
    ],
    [
        'name' => 'Cas 19: Paiement AUTRE',
        'description' => 'Facture avec mode AUTRE.',
        'request' => ['type' => 'FV', 'items' => [['name' => 'Article autre paiement', 'price' => 3500, 'quantity' => 1, 'taxGroup' => 'A']], 'payment' => [['name' => 'AUTRE', 'amount' => 3500]]],
    ],
    [
        'name' => 'Cas 20: AIB global',
        'description' => 'Facture avec option AIB.',
        'request' => ['type' => 'FV', 'aib' => 'A', 'items' => [['name' => 'Service AIB global', 'price' => 50000, 'quantity' => 1, 'taxGroup' => 'B']], 'payment' => [['name' => 'VIREMENT', 'amount' => 50000]]],
    ],
];
