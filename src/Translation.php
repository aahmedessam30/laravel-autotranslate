<?php

namespace Ahmedessam\LaravelAutotranslate;

use Illuminate\Support\Facades\Log;

class Translation
{
    private static array $patterns = [];

    /**
     * Set the patterns to be scanned.
     *
     * @param array $patterns
     * @return static
     */
    public static function setPatterns(array $patterns): static
    {
        self::$patterns = $patterns;
        return new static;
    }

    /**
     * Sync the translations.
     *
     * @param string|null $lang
     * @return string
     */
    public static function sync(string $lang = null): string
    {
        try {
            $translations = Scanner::setPatterns(self::$patterns)->scan();

            sort($translations);

            foreach ($translations as $translation) {

                $segments = explode('.', $translation);

                $segments = self::handleTranslationSegments($segments);
                $file     = $segments['file'];
                $key      = $segments['key'];

                foreach (self::getLanguages($lang) as $language) {
                    $file_path = self::getLangPath() . "/$language/$file.php";

                    if (!file_exists($file_path)) {
                        self::generateTranslationFile($file_path);
                    }

                    $content = file_get_contents($file_path);

                    if (str_contains($content, $key)) {
                        continue;
                    }

                    if (in_array($key, ["'", '"'])) {
                        continue;
                    }

                    $value = str($key)->replace('_', ' ')->replace('-', ' ')->title()->value();

                    $content = str_replace('];', "\t'$key' => '$value',\n];", $content);

                    file_put_contents($file_path, $content);
                }
            }

            return 'Translations synced successfully.';
        } catch (\Exception $e) {
            Log::error("Error in Translation@sync: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()} - {$e->getTraceAsString()}");
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Get the language path.
     *
     * @return string
     */
    private static function getLangPath(): string
    {
        if (!file_exists(resource_path('lang')) && !file_exists(lang_path())) {
            throw new \RuntimeException('Language path not found, please run php artisan lang:publish first.');
        }

        return file_exists(resource_path('lang')) ? resource_path('lang') : lang_path();
    }

    /**
     * Get the languages.
     *
     * @param string|null $lang
     * @return array
     */
    private static function getLanguages($lang = null): array
    {
        $languages = array_diff(scandir(self::getLangPath()), ['.', '..']);

        if ($lang) {
            return [$lang];
        }

        return $languages;
    }

    /**
     * Generate the translation file.
     *
     * @param string $file
     * @return void
     */
    private static function generateTranslationFile($file): void
    {
        $stub = StubGenerator::getStub('translation', __DIR__ . '/stubs');
        StubGenerator::saveStub($file, $stub);
    }

    private static function handleTranslationSegments($segments): array
    {
        if (count($segments) < 2) {
            $file = 'messages';
            $key  = $segments[0];
        } else {
            $file = $segments[0];
            $key  = $segments[1];
        }

        if (str_contains($key, ',')) {
            $key = explode("',", $key)[0];
        }

        return ['file' => $file, 'key' => $key];
    }
}
