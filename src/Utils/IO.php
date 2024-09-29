<?php

namespace LaravelTolgee\Utils;

class IO
{
    /**
     * Write a string to a file.
     */
    public static function write(string $content, string $path): void
    {
        file_put_contents($path, $content . PHP_EOL);
    }

    /**
     * Read existing translation file for the chosen language.
     */
    public static function readTranslationFile(string $language_path): array
    {
        $content = self::read($language_path);

        return JSON::jsonDecode($content);
    }

    /**
     * Read json file and convert it into an array of strings.
     */
    public static function read(string $path): false|string
    {
        if (!file_exists($path)) {
            return false;
        }

        return file_get_contents($path);
    }

    /**
     * Get language file path.
     */
    public static function languageFilePath(string $language): string
    {
        return function_exists('lang_path') ? lang_path("$language.json") : resource_path("lang/$language.json");
    }
}
