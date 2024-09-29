<?php

namespace LaravelTolgee\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use LaravelTolgee\Integration\Tolgee;
use LaravelTolgee\Utils\IO;
use LaravelTolgee\Utils\JSON;
use LaravelTolgee\Utils\VarExport;

class TolgeeService
{
    private array $config;

    public function __construct(private readonly Filesystem $files, private readonly Tolgee $tolgee)
    {
        $this->config = Config::get('tolgee');
    }

    /**
     * Sync translations from Tolgee service into a local files
     */
    public function syncTranslations(): true
    {
        $prepareWriteArray = [];
        $initial = $this->tolgee->getTranslationsRequest(parse: true);

        // Loop over translations pages, extract and prepare required data
        for ($page = 0; $page < $initial['page']['totalPages']; $page++) {
            $translations = $this->tolgee->getTranslationsRequest($page, true);

            foreach ($translations['_embedded']['keys'] as $translationItem) {
                $keyName = $translationItem['keyName'];
                $filePath = $translationItem['keyNamespace'];

                foreach ($translationItem['translations'] as $locale => $translation) {
                    if ($locale === 'en') {
                        continue;
                    }

                    $localPathName = Str::replace('/en', '/' . $locale, $filePath);
                    $writeArray = [$keyName => $translation['text']];

                    $prepareWriteArray[$localPathName] = array_key_exists($localPathName, $prepareWriteArray)
                        ? array_merge($prepareWriteArray[$localPathName], Arr::undot($writeArray))
                        : Arr::undot($writeArray);
                }
            }
        }

        // Write content info localized files
        foreach ($prepareWriteArray as $localPathName => $writeArray) {
            $fileContent = <<<'EOT'
                            <?php
                            
                            return {{translations}};
                            
                            EOT;
            $prettyWriteArray = VarExport::pretty(Arr::undot($writeArray), ['array-align' => true]);
            $fileContent = Str::replace('{{translations}}', $prettyWriteArray, $fileContent);

            $this->files->ensureDirectoryExists(dirname($localPathName));

            Str::contains($localPathName, '.json')
                ? IO::write(JSON::jsonEncode(Arr::undot($writeArray)), $localPathName)
                : IO::write($fileContent, $localPathName);
        }

        return true;
    }

    /**
     * Flush all keys on Tolgee service
     */
    public function deleteKeys(): PromiseInterface|Response
    {
        $ids = [];
        $init = $this->tolgee->getKeysRequest(parse: true);

        for ($page = 0; $page < $init['page']['totalPages']; $page++) {
            $data = $this->tolgee->getKeysRequest($page, true);
            $target = data_get($data, '_embedded.keys');
            $pluck = Arr::pluck($target, 'id');
            $ids = array_merge($ids, $pluck);
        }

        return $this->tolgee->deleteKeysRequest($ids);
    }

    /**
     * Prepare and sync keys from local files into Tolgee service
     * We can pass $withVendor var to include vendor files
     */
    public function importKeys(bool $withVendor = true): PromiseInterface|Response
    {
        $prepare = [];
        $import = [];

        // Prepare local .php files
        foreach ($this->files->directories($this->config['lang_path']) as $langPath) {
            $locale = basename($langPath);

            if ($locale !== 'en') {
                continue;
            }

            foreach ($this->files->allfiles($langPath) as $file) {
                $translations = include $file;

                $prepare[$file->getPathname()] = Arr::dot($translations);
            }
        }

        // Prepare vendor translations
        if ($this->files->exists($this->config['lang_path'] . '/vendor') && $withVendor) {
            foreach ($this->files->directories($this->config['lang_path'] . '/vendor') as $langPath) {
                foreach ($this->files->allFiles($langPath . '/en') as $file) {
                    $translations = include $file;

                    $prepare[$file->getPathname()] = Arr::dot($translations);
                }
            }
        }

        // Prepare json files translations
        foreach ($this->files->files($this->config['lang_path']) as $jsonFile) {
            if (!str_contains($jsonFile, '.json')) {
                continue;
            }

            $locale = basename($jsonFile, '.json');

            $translations = Lang::getLoader()->load($locale, '*', '*');
            $prepare[$jsonFile->getPathname()] = Arr::dot($translations);
        }

        // Remap everything into Tolgee request format
        foreach ($prepare as $namespace => $keys) {
            foreach ($keys as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $import[] = ['name' => $key, 'namespace' => $namespace, 'translations' => ['en' => $value]];
            }
        }

        return $this->tolgee->importKeysRequest($import);
    }
}
