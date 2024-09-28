<?php

namespace LaravelTolgee\Commands;

use Illuminate\Console\Command;
use LaravelTolgee\Services\TolgeeService;

class ImportKeysCommand extends Command
{
    protected $signature = 'tolgee:import-keys';

    public function __construct(private readonly TolgeeService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $response = $this->service->importKeys();

        if ($response->successful()) {
            $this->info('Keys are imported.');
        } else {
            $response->throw();
        }
    }
}
