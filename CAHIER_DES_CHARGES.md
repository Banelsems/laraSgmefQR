# DOCUMENT INTERNE
# CAHIER DES CHARGES
# LaraSgmefQR v3.0

## Améliorations du Package Laravel
### Spécification fonctionnelle et technique pour la mise en conformité DGI, la refonte architecturale et les nouvelles fonctionnalités du package de facturation électronique béninoise.

**Équipe Technique | Juin 2025**

---

## Table des matières

1. [Introduction et Contexte](#1-introduction-et-contexte)
2. [État Actuel du Package (v2.1.0)](#2-état-actuel-du-package-v210)
   - 2.1 Fonctionnalités existantes
   - 2.2 Architecture actuelle
   - 2.3 Limitations identifiées
3. [Exigences de Conformité DGI](#3-exigences-de-conformité-dgi)
   - 3.1 Auto-déclaration SFE
   - 3.2 Les 20 cas de test obligatoires
   - 3.3 Validation du QR Code
   - 3.4 Correspondance API e-MECeF
   - 3.5 Éléments de sécurité obligatoires
4. [Améliorations Architecturales](#4-améliorations-architecturales)
   - 4.1 Refactoring des DTOs
   - 4.2 Support Multi-IFU
   - 4.3 Files d'attente pour les factures (Job Queue)
   - 4.4 Middleware de sécurité renforcé
   - 4.5 Diagramme de séquence : création et confirmation
   - 4.6 Design patterns à implémenter
5. [Nouvelles Fonctionnalités](#5-nouvelles-fonctionnalités)
   - 5.1 Gestion des factures d'avoir (FA/EA)
   - 5.2 Export STAT
   - 5.3 Gestion avancée des taxes
   - 5.4 Multi-paiement et Webhooks
6. [Améliorations de l'Interface Utilisateur](#6-améliorations-de-linterface-utilisateur)
   - 6.1 Dashboard amélioré
   - 6.2 Design responsive et notifications
   - 6.3 Recherche et filtrage avancés
7. [Plan de Tests et Qualité](#7-plan-de-tests-et-qualité)
   - 7.1 Tests unitaires
   - 7.2 Tests d'intégration et de conformité
   - 7.3 Couverture cible et CI/CD
8. [Planning et Livrables](#8-planning-et-livrables)

---

## 1. Introduction et Contexte

Le système e-MECeF (electronic Mechanism for Centralized Invoicing) constitue la plateforme officielle de facturation électronique mise en place par la Direction Générale des Impôts (DGI) du Bénin. Ce dispositif réglementaire impose à tous les contribuables assujettis de produire des factures normalisées via un Système de Facturation Électronique (SFE) dûment homologué. L'objectif principal de cette démarche est de renforcer la transparence des transactions commerciales, de lutter contre la fraude fiscale et d'améliorer le recouvrement des impôts sur le territoire national. Chaque facture émise doit être vérifiable en ligne via le portail de la DGI à l'adresse https://developper.impots.bj/sygmef-test/verification, garantissant ainsi l'authenticité et la conformité de chaque document fiscal.

Le package LaraSgmefQR, dans sa version actuelle 2.1.0, offre une intégration Laravel avec l'API SyGM-eMCF. Il propose une architecture basée sur les principes SOLID, des DTOs pour le typage fort des données, un client HTTP robuste avec gestion des erreurs et des retry, une interface web de gestion des factures, un système d'événements Laravel, et des templates de factures multi-formats. Ce package est utilisé par plusieurs éditeurs de SFE et contributeurs au Bénin pour se conformer aux exigences de la DGI en matière de facturation électronique.

Le présent cahier des charges a pour objectif de guider le développement de la version 3.0 du package LaraSgmefQR. Il couvre quatre axes principaux : la mise en conformité complète avec les exigences de la DGI, notamment le processus d'auto-déclaration des SFE et les vingt cas de test obligatoires ; la refonte architecturale pour supporter le multi-IFU, les files d'attente asynchrones et les patterns de conception avancés ; l'ajout de nouvelles fonctionnalités telles que la gestion des factures d'avoir, l'export STAT et le multi-paiement ; enfin, l'amélioration de l'expérience utilisateur avec un dashboard moderne, des notifications en temps réel et un design responsive. Les documents de référence incluent la spécification de l'API e-MECeF v1.0 et la procédure d'auto-déclaration de SFE fournie par la DGI.

---

## 2. État Actuel du Package (v2.1.0)

### 2.1 Fonctionnalités existantes

La version 2.1.0 de LaraSgmefQR propose un ensemble de fonctionnalités solides qui constitue la base du package. Le client API (SgmefApiClient) offre une communication HTTP robuste avec l'API e-MECeF, incluant la gestion des timeouts, le retry automatique en cas d'échec, le logging complet des requêtes et réponses, ainsi qu'une validation stricte des données envoyées. L'architecture respecte les principes Clean Code et SOLID, avec des Data Transfer Objects (DTOs) pour le typage fort, des interfaces et contrats pour le découplage, et l'injection de dépendances pour l'inversion de contrôle. Le service principal, InvoiceManager, orchestre le cycle de vie complet des factures : création, confirmation, annulation et synchronisation avec l'API distante.

L'interface web intégrée fournit un dashboard avec des statistiques en temps réel, une gestion complète des factures avec formulaire de création, détails, confirmation, annulation et téléchargement PDF. Le package est totalement indépendant de tout système d'authentification, fonctionnant avec le concept d'opérateur configurable. Il est compatible avec tous les projets Laravel 10 et supérieurs, et supporte PHP 8.1+. Un système d'événements Laravel (InvoiceCreated, InvoiceConfirmed, InvoiceCancelled, InvoiceCreationFailed) permet une intégration fluide avec l'application hôte. Les exceptions personnalisées couvrent tous les cas d'erreur : InvoiceException, SgmefApiException, et leurs sous-classes spécialisées.

### 2.2 Architecture actuelle

Le package est organisé en trois couches principales :

1. **Couche Application** : L'application Laravel (le SFE du contribuable) interagit avec le package via les DTOs et les événements
2. **Couche Package** : LaraSgmefQR assure la médiation avec la gestion des DTOs, le service InvoiceManager, et le client HTTP
3. **Couche API** : Communication avec l'API e-MECeF de la DGI via le client HTTP spécialisé

Chaque composant est découplé grâce aux interfaces et à l'injection de dépendances, permettant une testabilité maximale et une grande flexibilité d'intégration dans des projets existants.

### 2.3 Limitations identifiées

Malgré ses qualités, la version actuelle présente plusieurs limitations qui empêchent une conformité totale avec les exigences de la DGI. Ces lacunes doivent être adressées en priorité dans la version 3.0 afin de permettre aux éditeurs de SFE d'obtenir l'approbation de la DGI lors du processus d'auto-déclaration. Les principales limitations sont les suivantes :

- **Absence du workflow d'auto-déclaration SFE** : le package ne guide pas l'utilisateur à travers le processus complet de vérification et de soumission des Annexe 1 et 2 à la DGI (emecefbenin@finances.bj)
- **Pas de support pour les factures d'avoir (FA/EA)** : le champ reference permettant de lier une facture d'avoir à sa facture d'origine n'est pas implémenté dans l'InvoiceRequestDto
- **Gestion mono-IFU uniquement** : un seul IFU est configurable via SGMEF_DEFAULT_IFU, ce qui ne permet pas la gestion multi-établissements pour les entreprises disposant de plusieurs points de vente
- **Champs manquants dans InvoiceItemDto** : les propriétés taxSpecific, originalPrice et priceModification requises par l'API e-MECeF pour certains scénarios de facturation ne sont pas présentes
- **Aucun export STAT** : la génération de fichiers récapitulatifs mensuels ou périodiques nécessaires au reporting fiscal auprès de la DGI n'est pas implémentée
- **Support limité des types de paiement** : seuls ESPECES, CARTEBANCAIRE, CHEQUE et VIREMENT sont documentés, alors que l'API supporte également MOBILEMONEY, CREDIT et AUTRE
- **Dashboard sans notifications temps réel** : les statistiques sont calculées à chaque chargement de page sans capacité de rafraîchissement automatique ou de notifications push
- **Pas de nettoyage automatique des factures en attente** : les factures non confirmées dans le délai de 2 minutes expirent côté e-MECeF mais ne sont pas nettoyées automatiquement dans la base locale

---

## 3. Exigences de Conformité DGI

Ce chapitre constitue le cœur du cahier des charges. La mise en conformité avec les exigences de la Direction Générale des Impôts est la priorité absolue de la version 3.0. Sans cette conformité, aucun éditeur de SFE ne peut obtenir l'approbation nécessaire à l'utilisation du package en production. Chaque sous-section détaille un aspect spécifique des exigences réglementaires et les adaptations techniques requises au niveau du package.

### 3.1 Auto-déclaration SFE

La procédure d'auto-déclaration est obligatoire pour tout éditeur de SFE ou contribuable utilisant son propre système de facturation. Elle vise à démontrer à la DGI que le SFE produit des factures conformes aux spécifications techniques de l'e-MECeF. Le processus se décompose en plusieurs étapes séquentielles que le package doit guider automatiquement.

- **Accès à la plateforme e-MECeF TEST** : obtenir les identifiants de test auprès de la DGI pour accéder à l'environnement de test à l'adresse https://developper.impots.bj/sygmef-emcf. Le package doit pouvoir basculer entre les environnements TEST et PRODUCTION via un paramètre de configuration unique.
- **Modification du SFE selon la documentation** : mettre en conformité toutes les sorties de factures (PDF, impressions) avec les éléments obligatoires : montants identiques, articles identiques, éléments de sécurité avec étiquettes et valeurs correctes, code QR obligatoire et scannable.
- **Vérification des factures sur le portail DGI** : chaque facture générée doit être vérifiable à l'adresse https://developper.impots.bj/sygmef-test/verification. Le format de la facture imprimée peut différer du format du serveur, mais tous les éléments (montants, articles, sécurité, QR) doivent être strictement identiques.
- **Remplissage des Annexe 1 et 2** : le formulaire d'auto-déclaration contient des cas de test pour divers types de factures et scénarios. Pour chaque cas, une capture d'écran de la facture doit être fournie avec toutes les données visibles et le QR code scannable.
- **Soumission à emecefbenin@finances.bj** : le dossier complet est envoyé par email à la DGI pour validation. Si des problèmes sont détectés, des mesures correctives doivent être appliquées et le processus répété jusqu'à obtention de l'approbation.

### 3.2 Les 20 cas de test obligatoires

L'auto-déclaration requiert la démonstration de vingt cas de test couvrant l'ensemble des types de factures, groupes de taxes et scénarios de paiement. Le tableau ci-dessous présente les quinze cas de test prioritaires que le package doit impérativement supporter. Les cinq cas restants devront être ajoutés en fonction du secteur d'activité spécifique de l'éditeur. Chaque cas de test doit être automatisé dans la suite de tests du package pour garantir la non-régression.

| No | Type | Groupe Taxe | Scenario de Test | Priorité |
|----|------|-------------|------------------|----------|
| 1 | FV | A | Facture de vente avec un seul article exonéré (groupe A, taux 0%) | Haute |
| 2 | FV | B | Facture de vente avec un seul article TVA 18% (groupe B) | Haute |
| 3 | FV | C | Facture de vente avec article TVA 18% + AIB 1% (groupe C) | Haute |
| 4 | FV | D | Facture de vente avec article TVA 18% + AIB 5% (groupe D) | Haute |
| 5 | FV | B+C | Facture multi-articles avec groupes de taxe mixtes (B et C) | Haute |
| 6 | FV | A+F | Facture avec article exonéré et article exportation (0%) | Moyenne |
| 7 | FA | B | Facture d'avoir avec référence à une facture de vente originale | Haute |
| 8 | FA | C | Facture d'avoir avec AIB, référence obligatoire à la facture initiale | Haute |
| 9 | EV | B | Export de facture de vente (type EV) pour déclaration | Haute |
| 10 | EA | B | Export de facture d'avoir (type EA) pour déclaration | Haute |
| 11 | FV | B | Facture avec paiement multiple (ESPECES + CARTEBANCAIRE) | Haute |
| 12 | FV | B | Facture avec paiement MOBILEMONEY | Moyenne |
| 13 | FV | B | Facture avec taxe spécifique (taxSpecific) sur un article | Haute |
| 14 | FV | B | Facture avec priceModification (remise ou majoration unitaire) | Haute |
| 15 | FV | B | Facture avec multi-paiement VIREMENT + CHEQUES + CREDIT | Moyenne |

Pour les cas de test 16 à 20, l'éditeur de SFE doit fournir des exemples supplémentaires liés à son secteur d'activité spécifique (par exemple : factures de services, factures avec remise globale, factures de cantine, factures avec exonération partielle). Si un cas de test parmi les 20 n'est pas applicable à l'activité de l'éditeur, celui-ci doit explicitement indiquer : "Notre SFE n'utilise pas ce type de facture". Dans ce cas, le SFE ne sera pas autorisé à produire ces types de factures après approbation.

### 3.3 Validation du QR Code

Le code QR est un élément de sécurité obligatoire de toute facture électronique confirmée. Il est généré par l'API e-MECeF lors de l'étape de confirmation (PUT /invoice/{uid}) et retourné dans le champ qr_code_data de la réponse. Ce QR code doit être imprimé sur chaque facture et scannable par tout lecteur QR standard. La vérification s'effectue sur le portail de la DGI à l'adresse https://developper.impots.bj/sygmef-test/verification. Le package doit intégrer un utilitaire de validation QR qui vérifie automatiquement que les données encodées dans le QR correspondent aux éléments de la facture (UID, montant, type). De plus, le package doit générer des images QR de haute résolution (minimum 300 DPI) pour garantir la lisibilité sur les impressions papier.

### 3.4 Correspondance API e-MECeF

Le tableau suivant dresse la correspondance entre les endpoints de l'API e-MECeF v1.0 et l'implémentation actuelle du package. Cette analyse permet d'identifier les manquements et les actions correctives nécessaires pour atteindre une couverture complète de l'API. Chaque endpoint est évalué en fonction de son statut d'implémentation et de l'action requise pour la version 3.0.

| Endpoint | Description | Statut Actuel | Action Requise |
|----------|-------------|---------------|----------------|
| POST /invoice | Création de facture | Implémenté | Ajouter taxSpecific, originalPrice, priceModification dans les items |
| PUT /invoice/{uid} | Confirmation de facture | Implémenté | Vérifier la gestion complète des éléments de sécurité |
| GET /invoice/{uid} | Récupération statut | Implémenté | Ajouter le support de la synchronisation incrémentale |
| DELETE /invoice/{uid} | Annulation de facture | Implémenté | Vérifier la cohérence avec l'enum InvoiceStatusEnum |
| POST /invoice/cancel | Annulation alternative | Non implémenté | Ajouter le support de cette méthode alternative |
| GET /tax-groups | Liste groupes de taxe | Non implémenté | Implémenter pour validation dynamique des groupes |
| GET /verification | Vérification facture | Non implémenté | Intégrer la vérification automatique post-confirmation |

### 3.5 Éléments de sécurité obligatoires

Lors de la confirmation d'une facture, l'API e-MECeF retourne un ensemble d'éléments de sécurité qui doivent être strictement reproduits sur la facture imprimée. Le package doit stocker et exposer ces éléments via le DTO InvoiceResponseDto et les inclure dans les templates de facture PDF. L'absence d'un seul de ces éléments constitue un motif de rejet lors de l'auto-déclaration. Les éléments de sécurité suivants sont obligatoires :

- **mecf_code** : le code MECeF officiel attribué par la DGI, identifiant unique de la facture dans le système fiscal
- **qr_code_data** : les données brutes encodées dans le QR code, permettant la vérification en ligne de la facture
- **Toutes les étiquettes de sécurité** retournées dans l'objet SecurityElementsDto de la réponse API, incluant notamment le numéro de contrôle, le hash de la facture, et les signatures numériques
- **La reproduction exacte des étiquettes (labels)** et de leurs valeurs correspondantes telles que retournées par l'API

---

## 4. Améliorations Architecturales

La version 3.0 du package nécessite une refonte architecturale significative pour répondre aux exigences de conformité DGI tout en améliorant la maintenabilité, l'extensibilité et les performances du code. Ce chapitre détaille les modifications structurelles planifiées, depuis le refactoring des DTOs jusqu'à l'implémentation de patterns de conception avancés.

### 4.1 Refactoring des DTOs

Les Data Transfer Objects constituent la couche de données fondamentale du package. L'analyse de l'API e-MECeF v1.0 a révélé plusieurs champs manquants dans l'InvoiceItemDto actuel. Ces champs sont nécessaires pour couvrir l'ensemble des cas de test obligatoires de la DGI. Le refactoring doit ajouter les propriétés suivantes à InvoiceItemDto :

```php
class InvoiceItemDto {
    public string $name;
    public float $price;
    public int $quantity;
    public string $taxGroup; // A, B, C, D, E, F
    public ?string $code;
    public ?string $description;

    // NOUVEAUX CHAMPS v3.0
    public ?float $taxSpecific; // Montant taxe spécifique
    public ?float $originalPrice; // Prix original avant modification
    public ?float $priceModification; // Remise ou majoration
    public ?string $reference; // Référence article externe
}
```

La méthode validate() de chaque DTO doit être enrichie pour vérifier la cohérence des nouvelles propriétés. Par exemple, si taxSpecific est défini, il doit être positif et inférieur au prix unitaire. Si priceModification est présent, originalPrice doit également être renseigné pour permettre le calcul du prix final. Ces validations garantiront que les données envoyées à l'API e-MECeF sont conformes dès la première requête, évitant les rejets et les allers-retours.

### 4.2 Support Multi-IFU

Les entreprises béninoises disposant de plusieurs établissements ou succursales doivent pouvoir émettre des factures avec des IFU différents depuis une même application Laravel. Le package actuel ne supporte qu'un seul IFU par installation via la variable d'environnement SGMEF_DEFAULT_IFU. La version 3.0 introduira une architecture multi-IFU basée sur un resolver d'interface :

```php
interface IfuResolverInterface {
    /**
     * Résoudre l'IFU à utiliser pour un contexte donné.
     */
    public function resolve(?string $context = null): string;
}

// Implémentation par défaut : IFU unique
class DefaultIfuResolver implements IfuResolverInterface {
    public function resolve(?string $ctx = null): string {
        return config('lara_sgmef_qr.company.default_ifu');
    }
}
```

Cette approche par interface permet à chaque application hôte de fournir sa propre logique de résolution IFU, que ce soit basée sur l'établissement de l'utilisateur connecté, sur un paramètre de requête, ou sur toute autre règle métier. Le binding Laravel sera effectué dans le ServiceProvider du package avec une portée scoped, permettant à différentes parties de l'application d'utiliser des IFU différents simultanément.

### 4.3 Files d'attente pour les factures (Job Queue)

La création et la confirmation de factures via l'API e-MECeF impliquent des appels réseau qui peuvent prendre plusieurs secondes. Pour éviter de bloquer les requêtes HTTP de l'utilisateur et garantir une bonne expérience, le package v3.0 intégrera un système de files d'attente Laravel. Deux jobs principaux seront implémentés : CreateInvoiceJob pour la création asynchrone et ConfirmInvoiceJob pour la confirmation. Le délai d'expiration de 2 minutes imposé par l'e-MECeF sera géré automatiquement par un job programmé de nettoyage des factures en attente expirées.

```php
class CreateInvoiceJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public InvoiceRequestDto $data
    ) {}

    public function handle(InvoiceManagerInterface $manager): Invoice {
        return $manager->createInvoice($this->data);
    }
}
```

### 4.4 Middleware de sécurité renforcé

La sécurité des échanges entre le SFE et l'API e-MECeF sera renforcée par l'ajout d'un middleware de sécurité au niveau du client HTTP. Ce middleware implémentera plusieurs mécanismes de protection : le rate limiting pour éviter de saturer l'API en cas de pic de facturation, la validation systématique du token JWT avant chaque requête, et la signature des requêtes pour garantir l'intégrité des données en transit. En cas d'échec d'authentification, le middleware déclenchera automatiquement un événement d'alerte pour permettre à l'application hôte de réagir rapidement (notification administrateur, basculement vers un mode dégradé, etc.).

### 4.5 Diagramme de séquence : création et confirmation

Le processus en deux étapes est le cœur du fonctionnement du package : la première requête (POST /invoice) crée la facture et obtient un UID unique avec les totaux calculés, puis la seconde requête (PUT /invoice/{uid}) confirme la facture et récupère les éléments de sécurité et le QR code. Le délai maximal entre les deux étapes est de 2 minutes, après quoi la facture non confirmée expire côté serveur e-MECeF.

### 4.6 Design patterns à implémenter

Pour améliorer la maintenabilité et l'extensibilité du package, trois patterns de conception seront systématiquement appliqués dans la version 3.0 :

- **Repository Pattern** : sera utilisé pour encapsuler l'accès aux données des factures, permettant de facilement changer la source de stockage (MySQL, PostgreSQL, ou même un stockage externe) sans modifier la logique métier
- **Strategy Pattern** : sera appliqué aux calculs de taxes, chaque groupe de taxe (A à F) étant implémenté comme une stratégie indépendante, facilitant l'ajout de nouveaux groupes ou la modification des règles de calcul existantes
- **Observer Pattern** : déjà partiellement implémenté via les événements Laravel, sera formalisé avec des listeners pour le nettoyage automatique, les notifications et le monitoring

---

## 5. Nouvelles Fonctionnalités

Au-delà de la mise en conformité, la version 3.0 introduit de nouvelles fonctionnalités qui répondent aux besoins concrets des éditeurs de SFE et des contributeurs.

### 5.1 Gestion des factures d'avoir (FA/EA)

Les factures d'avoir (FA pour les factures d'avoir de vente, EA pour les exports d'avoir) permettent de corriger ou d'annuler partiellement une facture de vente initiale. L'implémentation dans la version 3.0 nécessite l'ajout du champ reference dans l'InvoiceRequestDto. Ce champ obligatoire contient l'UID de la facture de vente originale à laquelle l'avoir se rapporte. L'API e-MECeF effectue automatiquement les vérifications de cohérence entre la facture d'avoir et la facture originale (montant de l'avoir inférieur ou égal au montant initial, même IFU, etc.). Le package doit valider ces règles en local avant l'envoi à l'API pour fournir des messages d'erreur clairs à l'utilisateur.

```php
$creditNote = InvoiceRequestDto::from([
    'ifu' => $resolver->resolve($etablissement),
    'type' => 'FA',
    'reference' => $originalInvoice->uid,
    'client' => [
        'name' => $originalInvoice->client_name,
        'ifu' => $originalInvoice->customer_ifu,
    ],
    'items' => $returnItems,
    'payment' => [['name' => 'VIREMENT', 'amount' => $returnTotal]],
]);
```

### 5.2 Export STAT

L'export STAT est un fichier récapitulatif périodique des factures émises, nécessaire au reporting fiscal auprès de la DGI. Le package v3.0 intégrera une commande Artisan et un service dédié pour générer ces exports. Le format de sortie sera configurable (CSV, XLSX, PDF) et inclura les informations suivantes : période de référence, nombre total de factures, montant total HT et TTC, montant total des taxes collectées, répartition par type de facture (FV, FA, EV, EA), et répartition par groupe de taxe. L'export pourra être généré manuellement via la commande Artisan ou automatiquement via un job programmé mensuel.

### 5.3 Gestion avancée des taxes

Le système de calcul des taxes sera refondu selon le pattern Strategy. Chaque groupe de taxe (A à F) sera implémenté comme une classe indépendante implémentant une interface TaxCalculationStrategyInterface. Cela permettra de calculer les taxes localement avant l'envoi à l'API, offrant ainsi un aperçu précis des montants TTC à l'utilisateur lors de la saisie de la facture.

| Groupe | Description | TVA | AIB | Taux Total |
|--------|-------------|-----|-----|------------|
| A | Exonéré | 0% | 0% | 0% |
| B | TVA standard | 18% | 0% | 18% |
| C | TVA + AIB 1% | 18% | 1% | 19% |
| D | TVA + AIB 5% | 18% | 5% | 23% |
| E | Régime spécial | Variable | Variable | Variable |
| F | Exportation | 0% | 0% | 0% |

### 5.4 Multi-paiement et Webhooks

Le support complet des types de paiement est essentiel pour couvrir les usages courants au Bénin. La version 3.0 documentera et validera les sept types de paiement suivants :

- **ESPECES** : paiement en espèces
- **VIREMENT** : virement bancaire
- **CARTEBANCAIRE** : carte de crédit ou débit
- **MOBILEMONEY** : paiement mobile, très répandu au Bénin via les plateformes comme MTN MoMo et Moov Money
- **CHEQUES** : chèque bancaire
- **CREDIT** : paiement à crédit avec échéancier
- **AUTRE** : tout autre mode de paiement

Le système de webhooks permettra aux applications hôtes de s'abonner aux changements de statut des factures et de déclencher des actions automatiques (envoi d'email, mise à jour du stock, synchronisation avec un ERP tiers, etc.). Les webhooks seront configurables via un système d'events Laravel avec support de queues pour un traitement asynchrone.

---

## 6. Améliorations de l'Interface Utilisateur

L'expérience utilisateur est un facteur déterminant pour l'adoption du package par les éditeurs de SFE et leurs clients finaux. La version 3.0 met l'accent sur un dashboard informatif, un design responsive adapté aux terminaux mobiles, des notifications en temps réel, et des fonctionnalités avancées de recherche et de filtrage.

### 6.1 Dashboard amélioré

Le dashboard sera entièrement repensé pour offrir une vue synthétique et actionnable de l'activité de facturation. Des cartes KPI seront affichées en haut de page avec les indicateurs suivants : nombre de factures créées aujourd'hui, cette semaine et ce mois ; taux d'erreur des appels API ; temps moyen de réponse de l'API e-MECeF ; et nombre de factures en attente de confirmation. Des graphiques chronologiques permettront de visualiser les tendances sur les 30 derniers jours. Un système d'alertes visuelles signalera les anomalies (pic d'erreurs, factures expirées, seuils de volume dépassés). Chaque carte KPI sera cliquable pour accéder au détail correspondant.

### 6.2 Design responsive et notifications

L'interface web adoptera une approche mobile-first avec un système de grille CSS flexible. Les formulaires de création de facture seront optimisés pour la saisie tactile avec des champs de taille adéquate, des sélecteurs de date natifs et des boutons d'action accessibles. Le mode sombre (dark mode) sera supporté via la propriété prefers-color-scheme du navigateur et un toggle manuel dans la barre de navigation. Les préférences de l'utilisateur seront persistées dans le localStorage du navigateur. Pour les notifications temps réel, deux approches seront proposées : le polling AJAX pour les installations ne disposant pas de WebSocket (vérification toutes les 30 secondes), et le broadcasting Laravel Echo via Pusher ou Reverb pour les installations avancées. Les notifications couvriront les événements de création, confirmation, échec et expiration des factures.

### 6.3 Recherche et filtrage avancés

La page de liste des factures sera enrichie avec un système de filtrage multi-critères complet. Les filtres disponibles incluront : plage de dates de création, statut de la facture (pending, confirmed, cancelled, error), type de facture (FV, FA, EV, EA), nom ou IFU du client, et tranche de montant. Les filtres seront combinables et persistants dans l'URL pour permettre le partage de vues filtrées. Un système de tri par colonnes sera également ajouté sur tous les champs pertinents (date, montant, statut, type). La recherche (full-text) sera implémentée sur le nom du client et la référence interne de la facture.

---

## 7. Plan de Tests et Qualité

La qualité du package est un engagement fondamental. La version 3.0 cible une couverture de tests de 95% minimum, avec une attention particulière portée aux tests de conformité DGI. Le plan de tests est structuré en quatre niveaux : tests unitaires, tests d'intégration, tests de conformité DGI automatisés, et tests de bout en bout.

### 7.1 Tests unitaires

Les tests unitaires couvriront l'ensemble des composants isolables du package :

- Validation des DTOs (vérifier que chaque DTO rejette les données invalides et accepte les données conformes)
- Calculs de taxes par groupe (vérifier les taux de TVA et AIB pour chaque groupe de A à F)
- Validation du format IFU (exactement 13 chiffres)
- Logique métier de l'InvoiceManager (gestion des transitions de statut, vérification des préconditions pour la confirmation et l'annulation)

Chaque test sera indépendant et reproductible, sans dépendance à un environnement externe. Les mocks seront utilisés pour simuler les dépendances (client API, repository).

### 7.2 Tests d'intégration et de conformité

Les tests d'intégration vérifieront le fonctionnement correct de l'ensemble du flux de facturation, du client HTTP jusqu'au stockage en base de données. Le client API sera mocké en utilisant les réponses typiques de l'e-MECeF pour simuler les cas nominaux et les cas d'erreur. Les tests de conformité DGI reprendront les vingt cas de test obligatoires de l'auto-déclaration. Chaque cas sera automatisé avec une fixture contenant les données d'entrée attendues et les assertions sur la réponse de l'API mockée (présence de l'UID, exactitude des totaux, présence des éléments de sécurité et du QR code). La validation QR sera également testée automatiquement en vérifiant que les données du QR correspondent aux éléments de la facture.

### 7.3 Couverture cible et CI/CD

L'objectif de couverture de tests est fixé à 95% minimum pour l'ensemble du code source du package. Un pipeline CI/CD sera configuré avec GitHub Actions pour exécuter automatiquement les tests à chaque push et pull request. Le pipeline comprendra trois jobs principaux :

- Le job de tests unitaires avec rapport de couverture
- Le job de tests d'intégration avec un environnement de test dédié
- Le job de déploiement sur l'environnement de staging après validation

| Type de Test | Outil | Cible | Fréquence |
|--------------|-------|-------|------------|
| Tests unitaires | Pest / PHPUnit | DTOs, Taxes, Validation IFU | À chaque commit |
| Tests d'intégration | Pest + HTTP Mocks | Flux complet facturation | À chaque commit |
| Conformité DGI | Pest + Fixtures | 20 cas de test auto-déclaration | Quotidien + pre-release |
| Couverture | PHPUnit --coverage | Objectif 95%+ | À chaque commit |
| CI/CD | GitHub Actions | Test / Coverage / Stage | Automatique |

---

## 8. Planning et Livrables

Le développement de la version 3.0 sera organisé en cinq phases progressives d'une durée de deux semaines chacune, soit un total de dix semaines de développement. Chaque phase produit des livrables tangibles et vérifiables, et se termine par des critères de validation stricts qui doivent être satisfaits avant de passer à la phase suivante. Ce découpage permet un suivi régulier de l'avancement et une livraison incrémentale de valeur.

| Phase | Période | Livrables | Critères de Validation |
|-------|---------|-----------|------------------------|
| Phase 1 | Semaines 1-2 | Refactoring des DTOs, ajout des champs manquants (taxSpecific, originalPrice, priceModification), écriture des tests unitaires de validation des DTOs | Tous les DTOs valides, couverture DTOs > 95% |
| Phase 2 | Semaines 3-4 | Support Multi-IFU avec IfuResolverInterface, gestion complète des factures d'avoir (FA/EA) avec reference, extension des types de paiement | Tests d'intégration multi-IFU et factures d'avoir passants |
| Phase 3 | Semaines 5-6 | Automatisation des 20 cas de test de conformité DGI, validation QR intégrée, nettoyage automatique des factures expirées | 20 cas de test DGI automatisés et passants, QR valide |
| Phase 4 | Semaines 7-8 | Dashboard amélioré avec KPIs, notifications temps réel, design responsive, mode sombre, recherche et filtrage avancés | Interface fonctionnelle et testée sur mobile et desktop |
| Phase 5 | Semaines 9-10 | Export STAT, pipeline CI/CD, documentation complète, préparation du dossier d'auto-déclaration, release candidate v3.0.0 | Pipeline CI/CD opérationnel, documentation complète, RC valide |

À l'issue de la Phase 5, le package sera en état de release candidate. Le dossier d'auto-déclaration complet, comprenant les captures d'écran des vingt cas de test, les Annexe 1 et 2 remplies, sera soumis à la DGI à l'adresse emecefbenin@finances.bj. Le package restera en version release candidate jusqu'à l'obtention de l'approbation formelle de la DGI, moment auquel la version 3.0.0 sera publiée officiellement. En cas de retour correctif de la DGI, les modifications seront appliquées dans une version 3.0.x et une nouvelle soumission sera effectuée dans les plus brefs délais.
