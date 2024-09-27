<?php

namespace LaravelTolgee\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TolgeeService
{
    private array $config;

    public function __construct(Application $app, private Filesystem $files)
    {
        $this->config = $app['config']['tolgee'];
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

        return Arr::dot($return);
    }

    public function importKeys(array $keys)
    {
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
            ->post($this->config['base_url'].'/v2/projects/'.$this->config['project_id'].'/keys/import', ['keys' => $prepareForTolgee]);

        return $client;
    }
}
