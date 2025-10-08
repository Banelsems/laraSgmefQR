<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Support;

use Illuminate\Foundation\Application;

/**
 * Helper pour gérer la compatibilité entre les versions Laravel
 */
class LaravelVersionHelper
{
    /**
     * Vérifie si la version Laravel est >= à la version spécifiée
     */
    public static function isVersion(string $version): bool
    {
        return version_compare(Application::VERSION, $version, '>=');
    }

    /**
     * Vérifie si c'est Laravel 10.x
     */
    public static function isLaravel10(): bool
    {
        return self::isVersion('10.0') && !self::isVersion('11.0');
    }

    /**
     * Vérifie si c'est Laravel 11.x
     */
    public static function isLaravel11(): bool
    {
        return self::isVersion('11.0') && !self::isVersion('12.0');
    }

    /**
     * Vérifie si c'est Laravel 12.x ou plus
     */
    public static function isLaravel12Plus(): bool
    {
        return self::isVersion('12.0');
    }

    /**
     * Retourne la version majeure de Laravel
     */
    public static function getMajorVersion(): int
    {
        $version = Application::VERSION;
        
        return (int) explode('.', $version)[0];
    }

    /**
     * Retourne le chemin des migrations selon la version Laravel
     */
    public static function getMigrationsPath(): string
    {
        if (self::isLaravel11()) {
            return database_path('migrations');
        }
        
        return database_path('migrations');
    }

    /**
     * Retourne la configuration des middlewares selon la version
     */
    public static function getMiddlewareConfig(): array
    {
        if (self::isLaravel11()) {
            return [
                'web' => ['web'],
                'api' => ['api'],
            ];
        }

        return [
            'web' => ['web'],
            'api' => ['api'],
        ];
    }

    /**
     * Retourne la méthode de publication des assets selon la version
     */
    public static function getPublishingMethod(): string
    {
        if (self::isLaravel12Plus()) {
            return 'publishesWith';
        }

        return 'publishes';
    }

    /**
     * Vérifie si les enums PHP sont supportés
     */
    public static function supportsEnums(): bool
    {
        return version_compare(PHP_VERSION, '8.1.0', '>=');
    }

    /**
     * Retourne la configuration de cache appropriée
     */
    public static function getCacheConfig(): array
    {
        $config = [
            'default_ttl' => 3600,
            'prefix' => 'lara_sgmef_qr',
        ];

        if (self::isLaravel11()) {
            $config['store'] = 'file';
        }

        return $config;
    }

    /**
     * Retourne la configuration des tests selon la version
     */
    public static function getTestConfig(): array
    {
        return [
            'phpunit_version' => self::isLaravel12Plus() ? '^11.0' : '^10.0',
            'testbench_version' => match (self::getMajorVersion()) {
                10 => '^8.0',
                11 => '^9.0',
                default => '^10.0',
            },
        ];
    }
}
