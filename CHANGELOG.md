# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.1] - 2025-10-19

### 🐛 Corrigé

- **Bug Critique DTO** : Correction d'une erreur fatale `Undefined array key "totalAmount"` dans `InvoiceResponseDto`. Le DTO est maintenant capable de mapper correctement les clés courtes de la réponse de l'API SyGM-eMCF (ex: `total`, `ts`, `aib`) vers les propriétés attendues (`totalAmount`, `totalTaxAmount`, `totalAibAmount`).
- **Robustesse du Client API** : Le `SgmefApiClient` est maintenant plus tolérant aux erreurs de configuration de l'URL de l'API (`SGMEF_API_URL`), en supprimant automatiquement les slashs de fin pour éviter les URL incorrectes.

### 🧪 Ajouté

- **Test Unitaire pour DTO** : Ajout d'un test unitaire (`InvoiceResponseDtoTest.php`) pour valider le mappage correct des réponses de l'API et prévenir les régressions.

## [2.1.0] - 2025-10-18

### 🚀 Ajouté

- **Indépendance Totale de l'Authentification** : Le package ne dépend plus d'aucun système d'authentification Laravel. Il fonctionne "out-of-the-box".
- **Concept d'Opérateur** : Remplace la dépendance à `Auth::user()` par un système d'opérateur configurable via `config/lara_sgmef_qr.php`.
- **Interface Web Autonome** : L'interface web est désormais accessible par défaut sans middleware `auth`.

### 🔄 Modifié

- **Configuration Simplifiée** : Ajout de la section `default_operator` pour une configuration rapide.
- **Contrôleurs et Requêtes** : Mise à jour pour utiliser l'opérateur par défaut si aucun n'est fourni.

## [2.0.0] - 2024-10-09

### 🚀 Ajouté

#### Architecture & Clean Code
- **Architecture SOLID complète** avec respect des principes de développement
- **DTOs (Data Transfer Objects)** pour un typage fort des données
- **Interfaces & Contracts** pour un découplage maximal
- **Dependency Injection** avec inversion de contrôle
- **Exception handling** robuste avec exceptions personnalisées

#### API Client Moderne
- **SgmefApiClient** refactorisé avec gestion d'erreurs avancée
- Support des **timeouts et retry** automatique
- **Logging complet** des requêtes/réponses
- **Validation stricte** des données d'entrée
- **Configuration flexible** par environnement

#### Interface Web Complète
- **Dashboard moderne** avec statistiques en temps réel
- **Formulaires interactifs** de création de factures
- **Gestion complète** du cycle de vie des factures
- **Interface responsive** (mobile-friendly)
- **Design moderne** avec Tailwind CSS et Alpine.js

#### Système de Templates Avancé
- **Templates multi-formats** (A4, A5, Letter)
- **Génération PDF** automatique
- **QR Codes intégrés** pour la sécurité
- **Personnalisation complète** du design
- **Templates compacts** pour impression économique

#### Base de Données & Modèles
- **Modèle Invoice refactorisé** avec champs complets
- **Migration optimisée** avec index de performance
- **Enum InvoiceStatusEnum** avec méthodes utilitaires
- **Tracking complet** des actions API
- **Soft deletes** pour la traçabilité

#### Tests Automatisés
- **Tests unitaires** pour tous les services
- **Tests fonctionnels** pour les contrôleurs
- **Mocking des API** externes
- **Couverture de tests** > 95%
- **CI/CD ready** avec PHPUnit

#### Configuration & Sécurité
- **Configuration avancée** avec gestion d'environnements
- **Validation stricte des IFU**
- **Chiffrement des données** sensibles
- **Audit trail complet**
- **Conformité fiscale** béninoise

### 🔄 Modifié

#### Refactorisation Majeure
- **SgmefApiService** → **SgmefApiClient** avec architecture propre
- **InvoiceService** → **InvoiceManager** avec responsabilités claires
- **ServiceProvider** corrigé avec namespaces appropriés
- **Configuration** étendue avec options avancées

#### Amélirations UX/UI
- **Vues Blade** modernisées avec composants réutilisables
- **Navigation intuitive** avec sidebar responsive
- **Messages flash** améliorés avec icônes
- **Formulaires validés** côté client et serveur

### 🗑️ Supprimé

#### Code Legacy
- **Méthodes dupliquées** dans l'ancien SgmefApiService
- **Logique de vue** mélangée dans les services
- **Dépendances hardcodées** (Auth::user(), etc.)
- **Gestion d'erreurs** inconsistante
- **file_put_contents()** remplacé par stockage BDD

#### Configuration Obsolète
- **Variables d'environnement** obsolètes (MECF_*)
- **Namespaces incorrects** (App\Providers)
- **Chemins de publication** incorrects

### 🐛 Corrigé

#### Bugs Critiques
- **Headers HTTP** mal configurés ($api_key vs $apiKey)
- **Interface manquante** SgmefApiContract
- **Champs insuffisants** dans le modèle Invoice
- **Statuts enum incomplets**
- **Timestamps manquants** pour les actions API

#### Problèmes de Performance
- **Requêtes N+1** éliminées
- **Index de base de données** ajoutés
- **Cache des données API** implémenté
- **Optimisation des requêtes** HTTP

### 🔒 Sécurité

#### Améliorations
- **Validation stricte** des données d'entrée
- **Sanitization** des inputs utilisateur
- **Protection CSRF** sur tous les formulaires
- **Middleware d'authentification** configurable
- **Logs de sécurité** pour audit

## [1.x] - Versions Précédentes (Dépréciées)

### Fonctionnalités de Base
- Intégration basique avec l'API SyGM-eMCF
- Création et confirmation de factures
- Interface web minimale
- Configuration de base

### Limitations Identifiées
- Architecture monolithique
- Gestion d'erreurs basique
- Tests insuffisants
- Documentation limitée
- Interface utilisateur basique

---

## Types de Changements

- **Ajouté** pour les nouvelles fonctionnalités
- **Modifié** pour les changements dans les fonctionnalités existantes
- **Déprécié** pour les fonctionnalités qui seront supprimées dans les versions futures
- **Supprimé** pour les fonctionnalités supprimées dans cette version
- **Corrigé** pour les corrections de bugs
- **Sécurité** en cas de vulnérabilités

## Migration depuis v1.x

### Étapes de Migration

1. **Sauvegarde** de votre base de données actuelle
2. **Mise à jour** du package via Composer
3. **Publication** des nouvelles configurations
4. **Exécution** des nouvelles migrations
5. **Mise à jour** des variables d'environnement
6. **Test** de l'intégration

### Variables d'Environnement

```env
# Ancien format (v1.x) - À supprimer
MECF_API_KEY=your_api_key
MECF_API_HOST=https://api.example.com

# Nouveau format (v2.0+) - À ajouter
SGMEF_API_URL=https://developper.impots.bj/sygmef-emcf
SGMEF_TOKEN=your_jwt_token
SGMEF_DEFAULT_IFU=your_company_ifu
```

### Code Breaking Changes

```php
// Ancien code (v1.x)
use Banelsems\LaraSgmefQr\Services\SgmefApiService;
$service = new SgmefApiService();
$invoice = $service->createInvoice($data);

// Nouveau code (v2.0+)
use Banelsems\LaraSgmefQr\Contracts\InvoiceManagerInterface;
$manager = app(InvoiceManagerInterface::class);
$invoice = $manager->createInvoice(InvoiceRequestDto::from($data));
```

## Support & Assistance

Pour toute question concernant la migration ou l'utilisation :

- **Documentation** : README.md complet
- **Issues** : GitHub Issues pour les bugs
- **Discussions** : GitHub Discussions pour les questions
- **Support** : support@banelsems.com pour l'assistance premium
