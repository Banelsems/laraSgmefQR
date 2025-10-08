# Guide de Contribution - LaraSgmefQR

Merci de votre intérêt pour contribuer à **LaraSgmefQR** ! Ce guide vous aidera à comprendre comment participer efficacement au développement du projet.

## 📋 Table des Matières

- [Code de Conduite](#code-de-conduite)
- [Comment Contribuer](#comment-contribuer)
- [Configuration de l'Environnement](#configuration-de-lenvironnement)
- [Standards de Développement](#standards-de-développement)
- [Processus de Pull Request](#processus-de-pull-request)
- [Tests](#tests)
- [Documentation](#documentation)
- [Signalement de Bugs](#signalement-de-bugs)
- [Demandes de Fonctionnalités](#demandes-de-fonctionnalités)

## 🤝 Code de Conduite

Ce projet adhère au [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/). En participant, vous vous engagez à respecter ce code.

### Nos Engagements

- **Respectueux** : Traiter tous les contributeurs avec respect
- **Inclusif** : Accueillir toutes les perspectives et expériences
- **Constructif** : Fournir des commentaires constructifs
- **Professionnel** : Maintenir un environnement professionnel

## 🚀 Comment Contribuer

### Types de Contributions Recherchées

1. **🐛 Correction de Bugs**
   - Signaler des bugs via les Issues
   - Proposer des corrections avec tests

2. **✨ Nouvelles Fonctionnalités**
   - Proposer des améliorations
   - Implémenter des fonctionnalités demandées

3. **📚 Documentation**
   - Améliorer la documentation existante
   - Ajouter des exemples d'utilisation
   - Traduire la documentation

4. **🧪 Tests**
   - Améliorer la couverture de tests
   - Ajouter des tests d'intégration
   - Optimiser les performances des tests

5. **🎨 Interface Utilisateur**
   - Améliorer l'UX/UI
   - Optimiser la responsivité
   - Ajouter de nouveaux templates

## ⚙️ Configuration de l'Environnement

### Prérequis

- **PHP** >= 8.1
- **Composer** >= 2.0
- **Node.js** >= 16.0 (pour les assets frontend)
- **Git** >= 2.30

### Installation

```bash
# 1. Fork et cloner le repository
git clone https://github.com/your-username/laraSgmefQR.git
cd laraSgmefQR

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node.js (si nécessaire)
npm install

# 4. Copier la configuration d'exemple
cp .env.example .env

# 5. Configurer les variables d'environnement
# Éditer .env avec vos paramètres de test

# 6. Exécuter les tests pour vérifier l'installation
composer test
```

### Configuration de l'IDE

#### VS Code (Recommandé)

```json
// .vscode/settings.json
{
    "php.validate.executablePath": "/usr/bin/php",
    "php.suggest.basic": false,
    "phpcs.enable": true,
    "phpcs.standard": "PSR12",
    "intelephense.diagnostics.undefinedTypes": false,
    "intelephense.diagnostics.undefinedFunctions": false
}
```

#### Extensions Recommandées

- **PHP Intelephense** : Autocomplétion PHP
- **PHP CS Fixer** : Formatage automatique
- **PHPUnit Test Explorer** : Exécution des tests
- **GitLens** : Intégration Git avancée

## 📏 Standards de Développement

### Standards de Code

#### PSR-12 : Style de Code

```php
<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Services;

use Banelsems\LaraSgmefQr\Contracts\SomeInterface;
use Banelsems\LaraSgmefQr\DTOs\SomeDto;

/**
 * Description de la classe
 */
final class ExampleService implements SomeInterface
{
    public function __construct(
        private readonly SomeDto $dto,
        private readonly string $parameter
    ) {}

    /**
     * Description de la méthode
     */
    public function performAction(array $data): bool
    {
        // Implémentation
        return true;
    }
}
```

#### Principes SOLID

1. **Single Responsibility** : Une classe = une responsabilité
2. **Open/Closed** : Ouvert à l'extension, fermé à la modification
3. **Liskov Substitution** : Les sous-types doivent être substituables
4. **Interface Segregation** : Interfaces spécifiques plutôt que générales
5. **Dependency Inversion** : Dépendre d'abstractions, pas de concrétions

#### Conventions de Nommage

```php
// Classes : PascalCase
class InvoiceManager {}

// Méthodes : camelCase
public function createInvoice() {}

// Variables : camelCase
$invoiceData = [];

// Constantes : SCREAMING_SNAKE_CASE
const MAX_RETRY_ATTEMPTS = 3;

// Interfaces : Interface suffix
interface InvoiceManagerInterface {}

// DTOs : Dto suffix
class InvoiceRequestDto {}

// Exceptions : Exception suffix
class SgmefApiException extends Exception {}
```

### Architecture

#### Structure des Dossiers

```
src/
├── Contracts/           # Interfaces
├── DTOs/               # Data Transfer Objects
├── Enums/              # Énumérations
├── Exceptions/         # Exceptions personnalisées
├── Http/
│   ├── Controllers/    # Contrôleurs web
│   └── Requests/       # Form Requests
├── Models/             # Modèles Eloquent
├── Providers/          # Service Providers
├── Services/           # Services métier
└── Tests/
    ├── Feature/        # Tests fonctionnels
    └── Unit/           # Tests unitaires
```

#### Injection de Dépendances

```php
// ✅ Bon : Injection via constructeur
public function __construct(
    private readonly InvoiceManagerInterface $invoiceManager
) {}

// ❌ Mauvais : Instanciation directe
public function someMethod()
{
    $manager = new InvoiceManager();
}
```

### Documentation du Code

#### PHPDoc

```php
/**
 * Crée une nouvelle facture électronique
 *
 * @param InvoiceRequestDto $invoiceData Données de la facture
 * @return Invoice Facture créée
 * 
 * @throws InvoiceException Si les données sont invalides
 * @throws SgmefApiException Si l'API retourne une erreur
 */
public function createInvoice(InvoiceRequestDto $invoiceData): Invoice
{
    // Implémentation
}
```

## 🔄 Processus de Pull Request

### 1. Préparation

```bash
# Créer une branche pour votre fonctionnalité
git checkout -b feature/nouvelle-fonctionnalite

# Ou pour un bugfix
git checkout -b fix/correction-bug-123
```

### 2. Développement

- Écrire le code en respectant les standards
- Ajouter des tests pour votre code
- Mettre à jour la documentation si nécessaire
- Vérifier que tous les tests passent

### 3. Validation Locale

```bash
# Exécuter les tests
composer test

# Vérifier le style de code
composer cs-check

# Corriger automatiquement le style
composer cs-fix

# Analyse statique
composer analyse
```

### 4. Commit

```bash
# Messages de commit conventionnels
git commit -m "feat: ajouter validation des IFU clients"
git commit -m "fix: corriger erreur de timeout API"
git commit -m "docs: mettre à jour guide d'installation"
```

#### Types de Commits

- **feat** : Nouvelle fonctionnalité
- **fix** : Correction de bug
- **docs** : Documentation uniquement
- **style** : Formatage, point-virgules manquants, etc.
- **refactor** : Refactorisation sans changement fonctionnel
- **test** : Ajout ou modification de tests
- **chore** : Maintenance, dépendances, etc.

### 5. Push et Pull Request

```bash
# Push de la branche
git push origin feature/nouvelle-fonctionnalite

# Créer la Pull Request sur GitHub
# Utiliser le template fourni
```

#### Template de Pull Request

```markdown
## Description
Brève description des changements

## Type de Changement
- [ ] Bug fix
- [ ] Nouvelle fonctionnalité
- [ ] Breaking change
- [ ] Documentation

## Tests
- [ ] Tests unitaires ajoutés/mis à jour
- [ ] Tests fonctionnels ajoutés/mis à jour
- [ ] Tous les tests passent

## Checklist
- [ ] Code respecte les standards PSR-12
- [ ] Documentation mise à jour
- [ ] Changelog mis à jour (si nécessaire)
```

## 🧪 Tests

### Structure des Tests

```php
<?php

namespace Banelsems\LaraSgmefQr\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Mockery;

class ExampleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Configuration des tests
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_perform_action_successfully(): void
    {
        // Arrange
        $service = new ExampleService();
        
        // Act
        $result = $service->performAction(['data' => 'test']);
        
        // Assert
        $this->assertTrue($result);
    }
}
```

### Commandes de Test

```bash
# Tous les tests
composer test

# Tests avec couverture
composer test-coverage

# Tests spécifiques
composer test -- --filter=ExampleServiceTest

# Tests en mode watch
composer test-watch
```

### Couverture de Tests

- **Minimum requis** : 80%
- **Objectif** : 95%+
- **Classes critiques** : 100% (Services, DTOs, Exceptions)

## 📚 Documentation

### Types de Documentation

1. **Code** : PHPDoc pour toutes les méthodes publiques
2. **API** : Documentation des endpoints
3. **Utilisateur** : README et guides d'utilisation
4. **Développeur** : Architecture et contribution

### Mise à Jour de la Documentation

```bash
# Générer la documentation API
composer docs-generate

# Vérifier les liens
composer docs-check

# Servir la documentation localement
composer docs-serve
```

## 🐛 Signalement de Bugs

### Template d'Issue Bug

```markdown
## Description du Bug
Description claire et concise du bug

## Étapes pour Reproduire
1. Aller à '...'
2. Cliquer sur '...'
3. Faire défiler jusqu'à '...'
4. Voir l'erreur

## Comportement Attendu
Description de ce qui devrait se passer

## Comportement Actuel
Description de ce qui se passe réellement

## Environnement
- OS: [ex: Ubuntu 20.04]
- PHP: [ex: 8.1.0]
- Laravel: [ex: 10.0]
- Package Version: [ex: 2.0.0]

## Logs/Screenshots
Ajouter des logs ou captures d'écran si pertinents
```

## ✨ Demandes de Fonctionnalités

### Template d'Issue Feature

```markdown
## Résumé de la Fonctionnalité
Brève description de la fonctionnalité souhaitée

## Motivation
Pourquoi cette fonctionnalité est-elle nécessaire ?

## Description Détaillée
Description complète de la fonctionnalité

## Alternatives Considérées
Autres solutions envisagées

## Implémentation Proposée
Idées sur comment implémenter cette fonctionnalité
```

## 🏆 Reconnaissance des Contributeurs

### Hall of Fame

Les contributeurs sont reconnus dans :
- README.md
- CONTRIBUTORS.md
- Notes de version
- Site web du projet

### Types de Contributions Reconnues

- **Code** : Développement de fonctionnalités
- **Documentation** : Amélioration de la doc
- **Tests** : Ajout/amélioration des tests
- **Design** : Amélioration de l'UI/UX
- **Traduction** : Localisation
- **Community** : Support communautaire

## 📞 Support

### Canaux de Communication

- **GitHub Issues** : Bugs et demandes de fonctionnalités
- **GitHub Discussions** : Questions et discussions
- **Discord** : Chat en temps réel
- **Email** : contribute@banelsems.com

### Temps de Réponse

- **Issues critiques** : 24-48h
- **Pull Requests** : 3-5 jours ouvrables
- **Questions générales** : 1 semaine

---

**Merci de contribuer à LaraSgmefQR ! 🚀**

Ensemble, nous construisons le meilleur package Laravel pour la facturation électronique béninoise.
