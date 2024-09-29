<?php

namespace LaravelTolgee\Integration;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class Tolgee
{
    private PendingRequest $client;
    private array $config;

    public function __construct()
    {
        $this->config = Config::get('tolgee');
        $this->client = Http::withHeader('X-API-Key', $this->config['api_key'])
            ->withUrlParameters(['project' => $this->config['project_id']])
            ->baseUrl($this->config['host']);
    }

    public function importKeysRequest(array $data): PromiseInterface|Response
    {
        return $this->client->post('/v2/projects/{project}/keys/import', ['keys' => $data]);
    }

    public function getKeysRequest(int $page = 0): PromiseInterface|Response
    {
        return $this->client
            ->withQueryParameters(['size' => 100, 'page' => $page])
            ->get('/v2/projects/{project}/keys');
    }

    public function deleteKeysRequest(array $data): PromiseInterface|Response
    {
        return $this->client->delete('/v2/projects/{project}/keys', ['ids' => $data]);
    }

    public function getTranslationsRequest(int $page = 0, bool $parse = false)
    {
        $request = $this->client->get('/v2/projects/{project}/translations', ['size' => 100, 'page' => $page]);

        return $parse ? $request->json() : $request;
    }
}
