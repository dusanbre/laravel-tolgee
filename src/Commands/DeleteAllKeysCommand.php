<?php

namespace LaravelTolgee\Commands;

use Illuminate\Console\Command;
use LaravelTolgee\Services\TolgeeService;

class DeleteAllKeysCommand extends Command
{
    protected $signature = 'tolgee:delete-all-keys';

    public function __construct(private readonly TolgeeService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $response = $this->service->deleteAllKeys();

        if ($response->successful()) {
            $this->info('All keys are deleted.');
        } else {
            $response->throw();
        }
    }
}
