<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Feature;

use Orchestra\Testbench\TestCase;

/**
 * Vérifie que l'ancien namespace de commande (Banelsems\LaraSgmefQr\Commands)
 * reste disponible via proxy deprecated pour préserver la compatibilité
 * avec les consumers externes qui pourraient référencer la classe.
 */
class LegacyCommandCompatibilityTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Banelsems\LaraSgmefQr\Providers\LaraSgmefQRServiceProvider::class];
    }

    public function test_legacy_namespace_class_still_exists(): void
    {
        $this->assertTrue(
            class_exists(\Banelsems\LaraSgmefQr\Commands\EmecefGenerateDeclaration::class),
            'L\'ancien namespace Banelsems\LaraSgmefQr\Commands\EmecefGenerateDeclaration doit rester disponible pour BC.'
        );
    }

    public function test_canonical_namespace_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Banelsems\LaraSgmefQr\Console\Commands\EmecefGenerateDeclaration::class),
            'Le namespace canonique Banelsems\LaraSgmefQr\Console\Commands\EmecefGenerateDeclaration doit exister.'
        );
    }

    public function test_legacy_class_extends_canonical(): void
    {
        $legacy = new \ReflectionClass(\Banelsems\LaraSgmefQr\Commands\EmecefGenerateDeclaration::class);
        $canonical = new \ReflectionClass(\Banelsems\LaraSgmefQr\Console\Commands\EmecefGenerateDeclaration::class);

        $this->assertTrue(
            $legacy->isSubclassOf($canonical),
            'La classe legacy doit hériter de la classe canonique (proxy pattern).'
        );
    }
}
