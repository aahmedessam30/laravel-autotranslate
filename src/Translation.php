<?php

namespace Ahmedessam\LaravelAutotranslate;

use Illuminate\Support\Facades\Log;

class Translation
{
    /**
     * Sync the translations.
     *
     * @param string|null $lang
     * @return string
     */
    public static function sync(string $lang = null): string
    {
        try {
            $translations = Scanner::scan();

            foreach ($translations as $translation => $attributes) {
                if (!is_string($translation)) {
                    continue; // Skip invalid translations
                }

                [$translationKey, $extractedAttributes] = self::extractAttributes($translation);
                $attributes = array_merge($attributes, $extractedAttributes);

                if (!$translationKey) {
                    continue; // Skip invalid translations
                }

                $segments = self::handleTranslationSegments($translationKey);
                $file = $segments['file'];
                $keys = $segments['keys'];

                $files = array_filter(self::getLanguages($lang), function ($file) {
                    return !(str_contains($file, '.json') || str_contains($file, 'vendor'));
                });

                foreach ($files as $language) {
                    $filePath = self::getLangPath() . "/$language/$file.php";

                    if (!file_exists($filePath)) {
                        self::generateTranslationFile($filePath);
                    }

                    $translationsArray = file_exists($filePath) ? include $filePath : [];
                    $translationsArray = self::addNestedKeys($translationsArray, $keys, array_keys($attributes));

                    $formattedContent = self::exportArray($translationsArray);
                    file_put_contents($filePath, $formattedContent);

                    self::handleAttributes($attributes, $language);
                }
            }

            return 'Translations synced successfully.';
        } catch (\Exception $e) {
            Log::error("Error in Translation@sync: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()} - {$e->getTraceAsString()}");
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Extracts attributes from translation strings
     *
     * @param string $translation
     * @return array [translation key, attributes]
     */
    private static function extractAttributes(string $translation): array
    {
        $translation = rtrim($translation, " '");

        // Match translation key and attributes (handling possible syntax variations)
        preg_match("/(.+?)', \[(.+?)\]/", $translation, $matches);

        if (isset($matches[1], $matches[2])) {
            $translationKey = trim($matches[1]);
            $attributes = self::parseAttributes($matches[2]);
            return [$translationKey, $attributes];
        }

        return [$translation, []];
    }

    /**
     * Parses attributes from a given attribute string
     *
     * @param string $attributesString
     * @return array
     */
    private static function parseAttributes(string $attributesString): array
    {
        $attributes = [];
        preg_match_all("/'(.+?)' => __\('(.+?)'\)/", $attributesString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (isset($match[1], $match[2])) {
                $attributeKey = trim($match[1]);
                $attributePath = trim($match[2]);

                if (!self::isValidKey($attributePath)) {
                    continue;
                }

                $attributeSegments = explode('.', $attributePath);
                $file = array_shift($attributeSegments);
                $attributes[$attributeKey] = ['file' => $file, 'keys' => $attributeSegments];
            }
        }

        return $attributes;
    }

    /**
     * Validates translation keys and ignores dynamic placeholders
     */
    private static function isValidKey(string $key): bool
    {
        return !preg_match('/{\$.*?}/', $key); // Ignore dynamic variables
    }

    private static function getLangPath(): string
    {
        return lang_path();
    }

    private static function getLanguages($lang = null): array
    {
        return $lang ? [$lang] : array_diff(scandir(self::getLangPath()), ['.', '..']);
    }

    private static function generateTranslationFile($filePath): void
    {
        file_put_contents($filePath, "<?php\n\nreturn [];\n");
    }

    private static function handleTranslationSegments($translation): array
    {
        $segments = explode('.', $translation);
        return ['file' => array_shift($segments), 'keys' => $segments];
    }

    private static function addNestedKeys(array $translations, array $keys, array $placeholders = []): array
    {
        $current = &$translations;
        foreach ($keys as $index => $key) {
            if (isset($current[$key]) && !is_array($current[$key])) {
                $current[$key] = ['value' => $current[$key]];
            }

            if ($index === array_key_last($keys)) {
                $translationString = ucfirst(str_replace('_', ' ', $key));

                if ($placeholders) {
                    $translationString .= ' ' . implode(' ', array_map(fn($p) => ":$p", $placeholders));
                }

                $current[$key] = $translationString;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }

                $current = &$current[$key];
            }
        }

        return $translations;
    }

    private static function handleAttributes(array $attributes, string $language): void
    {
        foreach ($attributes as $attrKey => $attrDetails) {
            $filePath = self::getLangPath() . "/$language/{$attrDetails['file']}.php";
            $translationsArray = file_exists($filePath) ? include $filePath : [];
            $translationsArray = self::addNestedKeys($translationsArray, $attrDetails['keys']);

            $formattedContent = self::exportArray($translationsArray);
            file_put_contents($filePath, $formattedContent);
        }
    }

    private static function exportArray(array $array): string
    {
        $arrayString = self::formatArray($array, 1);
        return "<?php\n\nreturn [\n" . $arrayString . "\n];\n";
    }

    private static function formatArray(array $array, int $indentLevel): string
    {
        $indent = str_repeat('    ', $indentLevel);
        $formatted = [];

        foreach ($array as $key => $value) {
            $formattedKey = var_export($key, true);

            if (is_array($value)) {
                $nested = self::formatArray($value, $indentLevel + 1);
                $formatted[] = "$indent$formattedKey => [\n$nested\n$indent],";
            } else {
                $formattedValue = var_export($value, true);
                $formatted[] = "$indent$formattedKey => $formattedValue,";
            }
        }

        return implode("\n", $formatted);
    }
}
