<?php

namespace LaravelTolgee\Integration;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
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
        $request = Http::withHeaders($this->headers)
            ->withUrlParameters($this->urlParams)
            ->baseUrl($this->host)
            ->get('/v2/projects/{project}/translations', ['page' => $page]);

        return $parse ? $request->json() : $request;
    }
}
