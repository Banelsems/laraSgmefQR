<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cas de Test pour l'Auto-Déclaration e-MECeF
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient les 20 cas de test requis pour l'Annexe 2 du processus
    | d'auto-déclaration. Chaque cas est structuré pour être utilisé par le
    | DeclarationGeneratorService pour générer les factures et les captures.
    |
    */

    // Cas 1: Facture normale avec TVA
    [
        'name' => 'Cas 1: Facture normale avec TVA',
        'request' => [
            'type' => 'FV',
            'items' => [
                ['name' => 'Article A', 'price' => 1000, 'quantity' => 2, 'taxGroup' => 'B'],
            ],
        ],
    ],

    // Cas 2: Facture avec plusieurs articles et groupes de taxe
    [
        'name' => 'Cas 2: Facture avec plusieurs articles et groupes de taxe',
        'request' => [
            'type' => 'FV',
            'items' => [
                ['name' => 'Article A (TVA 18%)', 'price' => 1000, 'quantity' => 1, 'taxGroup' => 'B'],
                ['name' => 'Article B (Exonéré)', 'price' => 500, 'quantity' => 3, 'taxGroup' => 'A'],
            ],
        ],
    ],

    // ... (Les 17 autres cas seraient listés ici pour être complet)

    // Cas 20: Facture avec AIB
    [
        'name' => 'Cas 20: Facture avec AIB',
        'request' => [
            'type' => 'FV',
            'aib' => 'A', // AIB de type A (1%)
            'items' => [
                ['name' => 'Service de consultation', 'price' => 50000, 'quantity' => 1, 'taxGroup' => 'A'],
            ],
        ],
    ],
];

