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

class TolgeeService
{
    private array $config;

    public function __construct(Application $app, private readonly Filesystem $files)
    {
        $this->config = $app['config']['tolgee'];
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
                'ids' => $keyIds
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
        $prepareForTolgee = [];

        foreach ($keys as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $prepareForTolgee[] = ['name' => $key, 'translations' => ['en' => $value]];
        }

        $client = Http::withHeader('X-API-Key', $this->config['api_key'])
            ->asJson()
            ->acceptJson()
            ->post(
                $this->config['base_url'].'/v2/projects/'.$this->config['project_id'].'/keys/import',
                ['keys' => $prepareForTolgee]
            );

        return $client;
    }

    public function importKeysPrepare(): array
    {
        $return = [];

        foreach ($this->files->directories($this->config['lang_path']) as $langPath) {
            $locale = basename($langPath);

            if ($locale === 'vendor') {
                continue;
            }

            if ($locale !== 'en') {
                continue;
            }

            foreach ($this->files->allfiles($langPath) as $file) {
                $info = pathinfo($file);

                $translations = include $file;

                $return[$locale][$info['filename']] = $translations;
            }
        }

        if ($this->files->exists($this->config['lang_path'].'/vendor')) {
            foreach ($this->files->directories($this->config['lang_path'].'/vendor') as $langPath) {
                $locale = basename($langPath);

                foreach ($this->files->allFiles($langPath.'/en') as $file) {
                    $info = pathinfo($file);

                    $keyName = Str::replace('lang/', '', $info['dirname']);
                    $keyName = Str::replace('/', '.', $keyName).'.'.$info['filename'];

                    $translations = include $file;

                    $return[$keyName] = $translations;
                }
            }
        }

        foreach ($this->files->files($this->config['lang_path']) as $jsonFile) {
            if (!str_contains($jsonFile, '.json')) {
                continue;
            }

            $locale = basename($jsonFile, '.json');

            $translations = Lang::getLoader()->load($locale, '*', '*');
            if ($translations && is_array($translations)) {
                foreach ($translations as $key => $value) {
                    $return[$locale]['json'][$key] = $value;
                }
            }
        }

        return Arr::dot($return);
    }
}
