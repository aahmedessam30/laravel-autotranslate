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

            sort($translations);

            foreach ($translations as $translation) {

                $segments = self::handleTranslationSegments($translation);
                $file     = $segments['file'];
                $key      = $segments['key'];
                $key      = str_replace("'", '', $key);
                $key      = str_replace('"', '', $key);

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

        $directory = config('autotranslate.default_directory', 'lang');

        if ($directory === 'lang') {
            return lang_path();
        }

        if ($directory === 'resource/lang') {
            return resource_path('lang');
        }

        if (!file_exists(base_path($directory)) && !mkdir($concurrentDirectory = base_path($directory), 0755, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        return base_path($directory);
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

    private static function handleTranslationSegments($translation): array
    {
        if (!str_contains($translation, '.')) {
            return ['file' => 'messages', 'key' => $translation];
        }

        $segments = explode('.', $translation);

        if (count($segments) < 2 || in_array($segments[1], ['', ' '])) {
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
