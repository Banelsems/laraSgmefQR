<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Support;

use Illuminate\Foundation\Application;

/**
 * Helper pour gérer la compatibilité entre les versions Laravel.
 *
 * La plupart des méthodes de cette classe sont des utilitaires de détection
 * de version. Depuis v4.0, le package fonctionne de manière identique sur
 * Laravel 10 à 13 sans conditionnelle version-spécifique. Les méthodes
 * sont conservées pour rétrocompatibilité mais la plupart n'ont plus de
 * différence fonctionnelle entre versions.
 */
class LaravelVersionHelper
{
    /**
     * Vérifie si la version Laravel est >= à la version spécifiée.
     */
    public static function isVersion(string $version): bool
    {
        return version_compare(Application::VERSION, $version, '>=');
    }

    /**
     * Vérifie si c'est Laravel 10.x.
     */
    public static function isLaravel10(): bool
    {
        return self::isVersion('10.0') && !self::isVersion('11.0');
    }

    /**
     * Vérifie si c'est Laravel 11.x.
     */
    public static function isLaravel11(): bool
    {
        return self::isVersion('11.0') && !self::isVersion('12.0');
    }

    /**
     * Vérifie si c'est Laravel 12.x ou plus.
     */
    public static function isLaravel12Plus(): bool
    {
        return self::isVersion('12.0');
    }

    /**
     * Retourne la version majeure de Laravel.
     */
    public static function getMajorVersion(): int
    {
        $version = Application::VERSION;

        return (int) explode('.', $version)[0];
    }

    /**
     * Retourne le chemin des migrations.
     *
     * Identique sur toutes les versions Laravel supportées (10-13).
     */
    public static function getMigrationsPath(): string
    {
        return database_path('migrations');
    }

    /**
     * Retourne la configuration des middlewares.
     *
     * Identique sur toutes les versions Laravel supportées (10-13).
     */
    public static function getMiddlewareConfig(): array
    {
        return [
            'web' => ['web'],
            'api' => ['api'],
        ];
    }

    /**
     * Retourne la méthode de publication des assets.
     *
     * @deprecated since 4.0.0, always returns 'publishes'. Scheduled for removal in 5.0.0.
     */
    public static function getPublishingMethod(): string
    {
        return 'publishes';
    }

    /**
     * Vérifie si les enums PHP sont supportés.
     *
     * @deprecated since 4.0.0, always true (composer.json requires PHP >= 8.1). Scheduled for removal in 5.0.0.
     */
    public static function supportsEnums(): bool
    {
        return true;
    }

    /**
     * Retourne la configuration de cache appropriée.
     */
    public static function getCacheConfig(): array
    {
        return [
            'default_ttl' => 3600,
            'prefix' => 'lara_sgmef_qr',
        ];
    }

    /**
     * Retourne la configuration des tests selon la version.
     */
    public static function getTestConfig(): array
    {
        return [
            'phpunit_version' => '^10.0|^11.0|^12.0',
            'testbench_version' => match (self::getMajorVersion()) {
                10 => '^8.0',
                11 => '^9.0',
                12 => '^10.0',
                default => '^11.0',
            },
        ];
    }
}
