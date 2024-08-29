<?php

namespace Ahmedessam\LaravelAutotranslate;

class Scanner
{
    private static array $translations        = [];
    private static array $allowedExtensions   = ['php', 'blade.php'];
    private static array $includedDirectories = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'tests'];
    private static array $patterns            = [
        '/__\(\'(.*?)\'\)/',
        '/__\(\"(.*?)\"\)/',
        '/trans\(\'(.*?)\'\)/',
        '/trans\(\"(.*?)\"\)/',
        '/@lang\(\'(.*?)\'\)/',
        '/@lang\(\"(.*?)\"\)/',
    ];

    private static function getPatterns(): array
    {
        return config('autotranslate.reset_patterns')
            ? config('autotranslate.patterns', [])
            : array_merge(self::$patterns, config('autotranslate.patterns', []));
    }

    public static function setPatterns(array $patterns): static
    {
        self::$patterns = array_merge(self::$patterns, $patterns);
        return new static();
    }

    public static function getDirectories(): array
    {
        return config('autotranslate.directories', self::$includedDirectories);
    }

    public static function scan(string $path = null): array
    {
        $path = $path ?? base_path();

        $directories = array_filter(scandir($path), function ($file) use ($path) {
            return is_dir("$path/$file") && !in_array($file, ['.', '..']) && in_array($file, self::getDirectories());
        });

        foreach ($directories as $dir) {
            self::scanDirectory("$path/$dir");
        }

        return array_unique(self::$translations);
    }

    private static function scanDirectory(string $dir): void
    {
        $files = array_filter(scandir($dir), fn($file) => !in_array($file, ['.', '..']));

        foreach ($files as $file) {
            $filePath = "$dir/$file";

            if (is_dir($filePath)) {
                self::scanDirectory($filePath);
            } elseif (self::isAllowedFile($filePath)) {
                self::$translations = [...self::$translations, ...self::getTranslations($filePath)];
            }
        }
    }

    private static function isAllowedFile(string $file): bool
    {
        return collect(self::$allowedExtensions)->contains(fn($ext) => str_ends_with($file, $ext));
    }

    private static function getTranslations($file): array
    {
        $content = file_get_contents($file);

        $matches = [];

        foreach (self::getPatterns() as $pattern) {
            if (preg_match_all($pattern, $content, $patternMatches)) {
                $matches = [...$matches, ...$patternMatches[1]];
            }
        }

        return array_filter(array_unique($matches));
    }
}
