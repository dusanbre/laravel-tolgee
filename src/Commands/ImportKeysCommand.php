<?php

namespace LaravelTolgee\Commands;

use Illuminate\Console\Command;
use LaravelTolgee\Services\TolgeeService;

class ImportKeysCommand extends Command
{
    protected $signature = 'tolgee:keys:sync {--with-vendors : Include vendor files}';

    protected $description = 'Command will sync all keys from local project files to Tolgee.This will not overwrite existing keys.';

    public function __construct(private readonly TolgeeService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->service->importKeys((bool)$this->option('with-vendors'));
        $response = $this->service->importKeys();

        if ($response->successful()) {
            $this->info('Keys are imported.');
        } else {
            $response->throw();
        }
    }
}
