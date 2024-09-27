<?php

namespace LaravelTolgee\Commands;

use Illuminate\Console\Command;
use LaravelTolgee\Services\TolgeeService;

class ImportKeysCommand extends Command
{
    protected $signature = 'tolgee:import-keys';

    public function __construct(private TolgeeService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $keys = $this->service->importKeysPrepare();

        $status = $this->service->importKeys($keys);

        if ($status->successful()) {
            $this->info('Keys are imported.');
        } else {
            $status->throw();
            $this->error('Something went wrong.');
        }
    }
}
