<?php

namespace LaravelTolgee\Integration;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class Tolgee
{
    private array $headers = [];
    private array $urlParams = [];

    private string $host = '';

    public function __construct()
    {
        $this->host = Config::get('tolgee.host');
        $this->urlParams = ['project' => Config::get('tolgee.project_id')];
        $this->headers = ['X-API-Key' => Config::get('tolgee.api_key')];
    }

    public function importKeysRequest(array $data): PromiseInterface|Response
    {
        return Http::withHeaders($this->headers)
            ->withUrlParameters($this->urlParams)
            ->baseUrl($this->host)
            ->post('/v2/projects/{project}/keys/import', ['keys' => $data]);
    }

    public function getKeysRequest(int $page = 0, bool $parse = false)
    {
        $request = Http::withHeaders($this->headers)
            ->withUrlParameters($this->urlParams)
            ->baseUrl($this->host)
            ->withQueryParameters(['page' => $page])
            ->get('/v2/projects/{project}/keys');

        return $parse ? $request->json() : $request;
    }

    public function deleteKeysRequest(array $data): PromiseInterface|Response
    {
        return Http::withHeaders($this->headers)
            ->withUrlParameters($this->urlParams)
            ->baseUrl($this->host)
            ->delete('/v2/projects/{project}/keys', ['ids' => $data]);
    }

    public function getTranslationsRequest(int $page = 0, bool $parse = false): PromiseInterface|Response|array
    {
        $languages = Http::withHeaders($this->headers)
            ->withUrlParameters($this->urlParams)
            ->withQueryParameters(['size' => 1000])
            ->baseUrl($this->host)
            ->get('v2/projects/{project}/languages')
            ->json('_embedded.languages');

        $languages = Arr::pluck($languages, 'tag');
        $queryLanguages = '';

        foreach ($languages as $language) {
            $queryLanguages .= "languages={$language}&";
        }

        $request = Http::withHeaders($this->headers)
            ->withUrlParameters($this->urlParams)
            ->baseUrl($this->host)
            ->get('/v2/projects/{project}/translations?' . $queryLanguages . 'page=' . $page);

        return $parse ? $request->json() : $request;
    }

    public function getAllTranslations()
    {
        $translations = [];
        $page = 0;
        
        do {
            $response = $this->getTranslationsRequest($page, true);
            $translations = array_merge($translations, $response['_embedded']['keys']);
            $page++;
        } while ($page < $response['page']['totalPages']);
        
        return $translations;
    }
}
