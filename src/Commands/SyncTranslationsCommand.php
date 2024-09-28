<?php

namespace LaravelTolgee\Commands;

use Illuminate\Console\Command;
use LaravelTolgee\Services\TolgeeService;

class SyncTranslationsCommand extends Command
{
    protected $signature = 'tolgee:translations:sync';

    protected $description = 'Command will sync translations from Tolgee to local files';

    public function __construct(private readonly TolgeeService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $res = $this->service->syncTranslations();
        dd($res);
    }

}
