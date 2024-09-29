<?php

namespace LaravelTolgee\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use LaravelTolgee\Utils\IO;
use LaravelTolgee\Utils\JSON;
use LaravelTolgee\Utils\VarExport;

class TolgeeService
{
    private array $config;

    public function __construct(Application $app, private readonly Filesystem $files)
    {
        $this->config = $app['config']['tolgee'];
    }

    public function syncTranslations(): true
    {
        $initial = $this->getAllTranslations();

        $prepareWriteArray = [];

        for ($page = 0; $page < $initial['page']['totalPages']; $page++) {
            $translations = $this->getAllTranslations($page);

            foreach ($translations['_embedded']['keys'] as $translationItem) {
                $keyName = $translationItem['keyName'];
                $filePath = $translationItem['keyNamespace'];

                foreach ($translationItem['translations'] as $locale => $translation) {
                    if ($locale === 'en') {
                        continue;
                    }

                    $localPathName = Str::replace('/en', '/' . $locale, $filePath);
                    $writeArray = [$keyName => $translation['text']]; // TODO: Finish this

                    $prepareWriteArray[$localPathName] = array_key_exists($localPathName, $prepareWriteArray)
                        ? array_merge($prepareWriteArray[$localPathName], Arr::undot($writeArray))
                        : Arr::undot($writeArray);
                }
            }
        }

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

    public function getAllTranslations(int $page = 0)
    {
        return Http::withHeader('X-API-Key', $this->config['api_key'])
            ->asJson()
            ->acceptJson()
            ->get(
                $this->config['base_url'] . '/v2/projects/' . $this->config['project_id'] . '/translations',
                ['size' => 20, 'page' => $page]
            )
            ->json();
    }

    public function deleteAllKeys()
    {
        $keyIds = [];
        $init = $this->getAllKeys();

        for ($page = 0; $page < $init['page']['totalPages']; $page++) {
            $data = $this->getAllKeys($page);
            $target = data_get($data, '_embedded.keys');
            $pluck = Arr::pluck($target, 'id');
            $keyIds = array_merge($keyIds, $pluck);
        }

        return Http::withHeader('X-API-Key', $this->config['api_key'])
            ->asJson()
            ->acceptJson()
            ->delete($this->config['base_url'] . '/v2/projects/' . $this->config['project_id'] . '/keys', [
                'ids' => $keyIds,
            ]);
    }

    public function getAllKeys(int $page = 0)
    {
        return Http::withQueryParameters(['size' => 100, 'page' => $page])
            ->withHeader('X-API-Key', $this->config['api_key'])
            ->asJson()
            ->acceptJson()
            ->get($this->config['base_url'] . '/v2/projects/' . $this->config['project_id'] . '/keys')
            ->json();
    }

    public function importKeys(): PromiseInterface|Response
    {
        $keys = $this->importKeysPrepare();

        return Http::withHeader('X-API-Key', $this->config['api_key'])
            ->asJson()
            ->acceptJson()
            ->post($this->config['base_url'] . '/v2/projects/' . $this->config['project_id'] . '/keys/import', ['keys' => $keys]);
    }

    public function importKeysPrepare(): array
    {
        $prepare = [];
        $return = [];

        foreach ($this->files->directories($this->config['lang_path']) as $langPath) {
            $locale = basename($langPath);

            if ($locale !== 'en') {
                continue;
            }

            foreach ($this->files->allfiles($langPath) as $file) {
                $translations = include_once $file;

                $prepare[$file->getPathname()] = Arr::dot($translations);
            }
        }

        if ($this->files->exists($this->config['lang_path'] . '/vendor')) {
            foreach ($this->files->directories($this->config['lang_path'] . '/vendor') as $langPath) {
                foreach ($this->files->allFiles($langPath . '/en') as $file) {
                    $translations = include $file;

                    $prepare[$file->getPathname()] = Arr::dot($translations);
                }
            }
        }

        foreach ($this->files->files($this->config['lang_path']) as $jsonFile) {
            if (!str_contains($jsonFile, '.json')) {
                continue;
            }

            $locale = basename($jsonFile, '.json');

            $translations = Lang::getLoader()->load($locale, '*', '*');
            $prepare[$jsonFile->getPathname()] = Arr::dot($translations);
        }

        foreach ($prepare as $namespace => $keys) {
            foreach ($keys as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $return[] = ['name' => $key, 'namespace' => $namespace, 'translations' => ['en' => $value]];
            }
        }

        return $return;
    }

    private function getLanguagesTags()
    {
        $langs = [];
        $init = $this->getAllLanguages();

        for ($page = 0; $page < $init['page']['totalPages']; $page++) {
            $data = $this->getAllLanguages($page);
            $target = data_get($data, '_embedded.languages');
            $pluck = Arr::pluck($target, 'tag');
            $langs = array_merge($langs, $pluck);
        }

        return $langs;
    }

    public function getAllLanguages(int $page = 0)
    {
        return Http::withHeader('X-API-Key', $this->config['api_key'])
            ->withQueryParameters(['page' => $page, 'pageSize' => 1000])
            ->asJson()
            ->acceptJson()
            ->get($this->config['base_url'] . '/v2/projects/' . $this->config['project_id'] . '/languages')
            ->json();
    }
}
