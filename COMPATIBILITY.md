# 🔄 Guide de Compatibilité Laravel - LaraSgmefQR

## 📊 **Matrice de Compatibilité**

| **Laravel Version** | **PHP Version** | **Status** | **Notes** |
|-------------------|----------------|------------|-----------|
| **10.x** | 8.1, 8.2, 8.3 | ✅ **Supporté** | Version LTS recommandée |
| **11.x** | 8.2, 8.3 | ✅ **Supporté** | Version stable actuelle |
| **12.x** | 8.2, 8.3 | ✅ **Supporté** | Dernière version |

## 🔧 **Exigences par Version**

### **Laravel 10.x (LTS)**
```json
{
  "require": {
    "php": "^8.1|^8.2|^8.3",
    "laravel/framework": "^10.0"
  }
}
```

### **Laravel 11.x**
```json
{
  "require": {
    "php": "^8.2|^8.3",
    "laravel/framework": "^11.0"
  }
}
```

### **Laravel 12.x**
```json
{
  "require": {
    "php": "^8.2|^8.3",
    "laravel/framework": "^12.0"
  }
}
```

## 🚀 **Installation par Version**

### **Installation Standard (Auto-détection)**
```bash
composer require banelsems/lara-sgmef-qr
```

### **Installation Spécifique Laravel 10**
```bash
composer require banelsems/lara-sgmef-qr "^2.0" --with-all-dependencies
```

### **Installation Spécifique Laravel 11**
```bash
composer require banelsems/lara-sgmef-qr "^2.0"
```

### **Installation Spécifique Laravel 12**
```bash
composer require banelsems/lara-sgmef-qr "^2.0"
```

## 🔍 **Différences par Version**

### **Laravel 10.x**
- **ServiceProvider** : Méthode `publishes()` standard
- **Migrations** : Support complet des migrations
- **Tests** : PHPUnit 10.x
- **Cache** : Configuration standard

### **Laravel 11.x**
- **ServiceProvider** : Nouvelles méthodes de publication
- **Migrations** : Structure de dossiers mise à jour
- **Tests** : PHPUnit 10.x/11.x
- **Cache** : Améliorations de performance

### **Laravel 12.x**
- **ServiceProvider** : API modernisée
- **Migrations** : Optimisations de performance
- **Tests** : PHPUnit 11.x
- **Cache** : Nouvelles fonctionnalités

## 🛠️ **Fonctionnalités Adaptatives**

### **Détection Automatique de Version**
Le package utilise `LaravelVersionHelper` pour détecter automatiquement la version Laravel et adapter son comportement :

```php
use Banelsems\LaraSgmefQr\Support\LaravelVersionHelper;

// Détection automatique
if (LaravelVersionHelper::isLaravel10()) {
    // Logique spécifique Laravel 10
}

if (LaravelVersionHelper::isLaravel11()) {
    // Logique spécifique Laravel 11
}

if (LaravelVersionHelper::isLaravel12Plus()) {
    // Logique spécifique Laravel 12+
}
```

### **Configuration Adaptative**
```php
// Configuration automatique selon la version
$config = LaravelVersionHelper::getCacheConfig();
$testConfig = LaravelVersionHelper::getTestConfig();
$middlewareConfig = LaravelVersionHelper::getMiddlewareConfig();
```

## 🧪 **Tests de Compatibilité**

### **Matrice de Tests**
| **Laravel** | **PHP 8.1** | **PHP 8.2** | **PHP 8.3** |
|-------------|-------------|-------------|-------------|
| **10.x** | ✅ Testé | ✅ Testé | ✅ Testé |
| **11.x** | ❌ N/A | ✅ Testé | ✅ Testé |
| **12.x** | ❌ N/A | ✅ Testé | ✅ Testé |

### **Commandes de Test**
```bash
# Test Laravel 10
composer test --with-laravel=10

# Test Laravel 11  
composer test --with-laravel=11

# Test Laravel 12
composer test --with-laravel=12
```

## 🔄 **Migration entre Versions**

### **De Laravel 10 vers 11**
```bash
# 1. Mise à jour Laravel
composer update laravel/framework:^11.0

# 2. Mise à jour du package (si nécessaire)
composer update banelsems/lara-sgmef-qr

# 3. Republier les assets
php artisan vendor:publish --tag=lara-sgmef-qr-config --force
```

### **De Laravel 11 vers 12**
```bash
# 1. Mise à jour Laravel
composer update laravel/framework:^12.0

# 2. Vérifier la compatibilité
php artisan package:check-compatibility

# 3. Republier si nécessaire
php artisan vendor:publish --tag=lara-sgmef-qr-migrations --force
```

## ⚠️ **Limitations Connues**

### **Laravel 10.x**
- Certaines fonctionnalités de cache avancées non disponibles
- Interface de tests légèrement différente

### **Laravel 11.x**
- Migration de la structure des middlewares
- Nouvelles conventions de nommage

### **Laravel 12.x**
- Changements dans l'API des ServiceProviders
- Nouvelles exigences de sécurité

## 🆘 **Résolution de Problèmes**

### **Erreur de Version PHP**
```bash
# Vérifier la version PHP
php --version

# Mettre à jour si nécessaire (via Homebrew sur macOS)
brew update && brew upgrade php
```

### **Conflit de Dépendances**
```bash
# Résolution forcée
composer update --with-all-dependencies

# Ou installation propre
rm composer.lock vendor/ -rf
composer install
```

### **Problèmes de Cache**
```bash
# Nettoyer le cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Nettoyer le cache Composer
composer clear-cache
```

## 📞 **Support**

Pour toute question de compatibilité :
- **Issues GitHub** : https://github.com/Banelsems/laraSgmefQR/issues
- **Discussions** : https://github.com/Banelsems/laraSgmefQR/discussions
- **Email** : banelsemassoussi@gmail.com

---

**✅ Le package LaraSgmefQR est conçu pour s'adapter automatiquement à votre version Laravel !**
