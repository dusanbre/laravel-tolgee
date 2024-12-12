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
        
        // Loop over translations pages, extract and prepare required data
        foreach ($this->tolgee->getAllTranslations() as $translationItem) {
            $keyName = $translationItem['keyName'];
            $filePath = $translationItem['keyNamespace'];

            foreach ($translationItem['translations'] as $locale => $translation) {
                if (
                    ($locale === $this->config['locale'] && !$this->config['override']) ||
                    (!in_array($translation['state'], $this->config['accepted_states']))
                ) {
                    continue;
                }

                $localPathName = Str::replace('/'.$this->config["locale"], '/' . $locale, $filePath);
                
                if(empty($prepareWriteArray[$localPathName])){
                    $prepareWriteArray[$localPathName] = $this->getFileTranslationsArray($localPathName);
                }
                
                self::setValueByDotNotation($prepareWriteArray[$localPathName], $keyName, $translation['text']);
            }
        }

        // Write content info localized files
        foreach ($prepareWriteArray as $localPathName => $writeArray) {
            $fileContent = <<<'EOT'
                            <?php
                            
                            return {{translations}};
                            
                            EOT;
            $prettyWriteArray = VarExport::pretty($writeArray, ['array-align' => $this->config['pretty_file']]);
            $fileContent = Str::replace('{{translations}}', $prettyWriteArray, $fileContent);
            
            IO::write(Str::contains($localPathName, '.json') ? JSON::jsonEncode($writeArray) : $fileContent, $localPathName);
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
        $langPath = $this->config['lang_path']."/".$this->config['locale'];
        
        if ($this->config['lang_subfolder']) {
            $langPath .= '/'.$this->config['lang_subfolder'];
        }
        
        foreach ($this->files->allfiles($langPath) as $file) {
            $prepare[$file->getPathname()] = Arr::dot(include $file);
        }

        if (!$this->config['lang_subfolder']) {
            // Prepare vendor translations
            if (is_file(base_path($this->config['lang_path'] . '/vendor')) && $withVendor) {
                foreach ($this->files->allFiles($this->config['lang_path'] . '/vendor/'.$this->config['locale']) as $file) {
                    $prepare[$file->getPathname()] = Arr::dot(include $file);
                }
            }

            // Prepare json files translations
            foreach ($this->files->files($this->config['lang_path']) as $jsonFile) {
                if (!str_contains($jsonFile, '.json')) {
                    continue;
                }

                $locale = basename($jsonFile, '.json');

                if ($locale !== $this->config['locale']) {
                    continue;
                }

                $prepare[$jsonFile->getPathname()] = Arr::dot(Lang::getLoader()->load($locale, '*', '*'));
            }
        }

        // Remap everything into Tolgee request format
        foreach ($prepare as $namespace => $keys) {
            foreach ($keys as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                $import[] = ['name' => $key, 'namespace' => $namespace, 'translations' => [$this->config['locale'] => $value]];
            }
        }

        return $this->tolgee->importKeysRequest($import);
    }
    
    /**
     * Get an array of translations from a file
     */
    private function getFileTranslationsArray($filePath){
        $data = [];
        
        if(!is_file(base_path($filePath))){
            return $data;
        }
        
        if(Str::contains($filePath, '.json')){
            $data = json_decode(IO::read($filePath), true);
        }
        else{
            $data = include base_path($filePath);
        }
        
        return $data;
    }
    
    /**
     * Set a value in an array according to the dot notation provided
     */
    private static function setValueByDotNotation(&$array, $notation, $value) {
        $keys = explode('.', $notation);
        $current = &$array;
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        
        $current = $value;
    }
}
