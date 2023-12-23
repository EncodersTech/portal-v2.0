<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DwollaService;

class RetrieveDwollaWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwolla:retrieve-webhook {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve a dwolla webhook';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    private $dwollaService;

    public function __construct(DwollaService $dwollaService)
    {
        parent::__construct();
        $this->dwollaService = $dwollaService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $webhook = $this->dwollaService->retrieveWebhook($id);
        $this->info(json_encode($webhook, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}