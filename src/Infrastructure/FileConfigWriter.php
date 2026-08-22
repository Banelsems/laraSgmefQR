<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Infrastructure;

use Banelsems\LaraSgmefQr\Contracts\ConfigWriterInterface;
use RuntimeException;

/**
 * Écriture atomique et ciblée d'un fichier de configuration PHP.
 *
 * @deprecated since 4.0.0, scheduled for removal in 5.0.0.
 *             Préférez les variables d'environnement (.env) pour la
 *             configuration runtime. L'écriture du fichier de config
 *             détruit les appels env() pour les clés modifiées.
 *
 * Contrairement à l'ancienne approche var_export() qui reconstruisait
 * tout le fichier (détruisant tous les appels env()), cette implémentation
 * fait des remplacements ciblés : seules les clés modifiées sont affectées,
 * les autres préservent leurs appels env().
 *
 * Écriture atomique : fichier temporaire + rename, avec verrou exclusif.
 */
final class FileConfigWriter implements ConfigWriterInterface
{
    public function write(string $path, array $changes): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Fichier de configuration introuvable : {$path}");
        }

        if (!is_writable($path)) {
            throw new RuntimeException("Fichier de configuration non inscriptible : {$path}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Impossible de lire le fichier de configuration : {$path}");
        }

        foreach ($changes as $key => $value) {
            $content = $this->replaceValue($content, $key, $value);
        }

        $this->atomicWrite($path, $content);
    }

    /**
     * Remplace la valeur d'une clé dans le contenu du fichier.
     *
     * Supporte la notation par points pour les clés imbriquées
     * (ex: 'http_options.timeout').
     */
    private function replaceValue(string $content, string $key, mixed $value): string
    {
        $formattedValue = $this->formatPhpValue($value);

        if (str_contains($key, '.')) {
            return $this->replaceNestedValue($content, $key, $formattedValue);
        }

        return $this->replaceTopLevelValue($content, $key, $formattedValue);
    }

    /**
     * Remplace une clé de premier niveau.
     *
     * Gère les valeurs sur une seule ligne et multi-lignes.
     */
    private function replaceTopLevelValue(string $content, string $key, string $formattedValue): string
    {
        // Cas 1 : valeur sur une seule ligne
        // Pattern: 'key' => value,
        $pattern = "/^(\s*'" . preg_quote($key, '/') . "'\s*=>\s*)(.+)(,)\s*$/m";

        if (preg_match($pattern, $content)) {
            $result = preg_replace($pattern, "$1" . $formattedValue . "$3", $content, 1);
            if ($result !== null) {
                return $result;
            }
        }

        // Cas 2 : valeur multi-lignes se terminant par ),
        // Pattern: 'key' => ... \n ... ),
        $pattern = "/^(\s*'" . preg_quote($key, '/') . "'\s*=>\s*)([\s\S]+?)(\),)\s*$/m";

        if (preg_match($pattern, $content)) {
            $result = preg_replace($pattern, "$1" . $formattedValue . ",\n", $content, 1);
            if ($result !== null) {
                return $result;
            }
        }

        throw new RuntimeException("Clé de configuration '{$key}' introuvable dans le fichier.");
    }

    /**
     * Remplace une clé imbriquée (notation par points).
     *
     * Ex: 'http_options.timeout' → trouve la section 'http_options'
     * et remplace 'timeout' à l'intérieur.
     */
    private function replaceNestedValue(string $content, string $dottedKey, string $formattedValue): string
    {
        $parts = explode('.', $dottedKey, 2);
        $section = $parts[0];
        $subKey = $parts[1];

        // Trouver la section: 'section' => [ ... ],
        $sectionPattern = "/^(\s*'" . preg_quote($section, '/') . "'\s*=>\s*\[)([\s\S]+?)(\])/m";

        if (!preg_match($sectionPattern, $content, $sectionMatches)) {
            throw new RuntimeException("Section '{$section}' introuvable dans le fichier de configuration.");
        }

        $sectionContent = $sectionMatches[2];

        // Remplacer la sous-clé dans le contenu de la section
        $subPattern = "/^(\s*'" . preg_quote($subKey, '/') . "'\s*=>\s*)(.+)(,)\s*$/m";

        if (!preg_match($subPattern, $sectionContent)) {
            throw new RuntimeException("Clé imbriquée '{$dottedKey}' introuvable dans la section '{$section}'.");
        }

        $newSectionContent = preg_replace($subPattern, "$1" . $formattedValue . "$3", $sectionContent, 1);
        if ($newSectionContent === null) {
            throw new RuntimeException("Échec du remplacement pour '{$dottedKey}'.");
        }

        return str_replace(
            $sectionMatches[0],
            $sectionMatches[1] . $newSectionContent . $sectionMatches[3],
            $content
        );
    }

    /**
     * Formate une valeur PHP pour l'écriture dans un fichier de config.
     */
    private function formatPhpValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return (string) $value;
        }

        if ($value === null) {
            return 'null';
        }

        // Fallback pour les tableaux et autres types
        return var_export($value, true);
    }

    /**
     * Écriture atomique : fichier temporaire + verrou + rename.
     */
    private function atomicWrite(string $path, string $content): void
    {
        $directory = dirname($path);
        $tempPath = $path . '.tmp.' . bin2hex(random_bytes(8));

        $fp = @fopen($tempPath, 'x+');
        if ($fp === false) {
            throw new RuntimeException("Impossible de créer le fichier temporaire : {$tempPath}");
        }

        try {
            // Verrou exclusif
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Impossible d\'obtenir le verrou sur le fichier temporaire.');
            }

            $written = fwrite($fp, $content);
            if ($written === false || $written !== strlen($content)) {
                throw new RuntimeException('Écriture incomplète du fichier temporaire.');
            }

            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);

            // Rename atomique
            if (!rename($tempPath, $path)) {
                throw new RuntimeException('Échec du rename atomique du fichier de configuration.');
            }
        } catch (\Throwable $e) {
            if (is_resource($fp)) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            throw $e;
        }
    }
}
