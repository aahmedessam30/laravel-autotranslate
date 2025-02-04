<?php

namespace Ahmedessam\LaravelAutotranslate;

class Scanner
{
    private static array $translations        = [];
    private static array $allowedExtensions   = ['php', 'blade.php'];
    private static array $includedDirectories = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'tests'];
    private static array $patterns            = [
        '/__\(\'(.*?)\'(?:, \[(.*?)\])?\)/', // Matches __('messages.key', ['model' => __('models.value')])
        '/__\(\"(.*?)\"(?:, \[(.*?)\])?\)/',
        '/trans\(\'(.*?)\'(?:, \[(.*?)\])?\)/',
        '/trans\(\"(.*?)\"(?:, \[(.*?)\])?\)/',
        '/@lang\(\'(.*?)\'(?:, \[(.*?)\])?\)/',
        '/@lang\(\"(.*?)\"(?:, \[(.*?)\])?\)/',
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

        return self::$translations;
    }

    private static function scanDirectory(string $dir): void
    {
        $files = array_filter(scandir($dir), fn($file) => !in_array($file, ['.', '..']));

        foreach ($files as $file) {
            $filePath = "$dir/$file";

            if (is_dir($filePath)) {
                self::scanDirectory($filePath);
            } elseif (self::isAllowedFile($filePath)) {
                self::processFile($filePath);
            }
        }
    }

    private static function isAllowedFile(string $file): bool
    {
        return collect(self::$allowedExtensions)->contains(fn($ext) => str_ends_with($file, $ext));
    }

    private static function processFile(string $file): void
    {
        $content = file_get_contents($file);

        foreach (self::getPatterns() as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $key = trim($match[1]);  // Main translation key

                    if (!empty($match[2])) {
                        // Parse attributes from `['model' => __('models.package')]`
                        self::extractAttributes($key, $match[2]);
                    } else {
                        // Only add the translation key if it doesn't already exist
                        if (!isset(self::$translations[$key])) {
                            self::$translations[$key] = [];
                        }
                    }
                }
            }
        }
    }

    /**
     * Extracts attributes like ['model' => __('models.package')]
     */
    private static function extractAttributes(string $key, string $attributesString): void
    {
        $attributes = [];

        preg_match_all("/'(.+?)' => __\('(.+?)'\)/", $attributesString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (isset($match[1], $match[2])) {
                $attributeKey = trim($match[1]);  // Example: model
                $attributePath = trim($match[2]); // Example: models.package

                $attributeSegments = explode('.', $attributePath);
                $file = array_shift($attributeSegments);

                if (!isset(self::$translations[$key])) {
                    self::$translations[$key] = [];
                }

                self::$translations[$key][$attributeKey] = [
                    'file' => $file,
                    'keys' => $attributeSegments,
                ];
            }
        }
    }
}
