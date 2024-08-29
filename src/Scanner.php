<?php

namespace Ahmedessam\LaravelAutotranslate;

class Scanner
{
    private static array $translations = [];
    private static array $includedDirectories = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'tests'];
    private static array $patterns = [
        '/__\(\'(.*?)\'\)/',
        '/__\(\"(.*?)\"\)/',
        '/trans\(\'(.*?)\'\)/',
        '/trans\(\"(.*?)\"\)/',
        '/@lang\(\'(.*?)\'\)/',
        '/@lang\(\"(.*?)\"\)/',
    ];

    private static function getPatterns()
    {
        if (config('autotranslate.reset_patterns')) {
            return config('autotranslate.patterns');
        }

        return array_merge(self::$patterns, config('autotranslate.patterns'));
    }

    public static function scan($path = null): array
    {
        $path = $path ?? base_path();

        $files = scandir($path);

        $files = array_filter($files, fn($file) => !in_array($file, ['.', '..', ...array_diff($files, self::$includedDirectories)], true));

        foreach ($files as $file) {
            $file = "$path/$file";

            if (is_dir($file)) {
                self::getFiles($file);
            } else {
                self::$translations = [...self::$translations, ...self::getTranslations($file)];
            }
        }

        return array_unique(self::$translations);
    }

    private static function getFiles($dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $file = "$dir/$file";

            if (is_dir($file)) {
                self::getFiles($file);
            } else {
                self::$translations = [...self::$translations, ...self::getTranslations($file)];
            }
        }

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

    public static function setPatterns(array $patterns): static
    {
        self::$patterns = array_merge(self::$patterns, $patterns);
        return new static();
    }
}
