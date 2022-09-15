<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DwollaService;

class RetrieveDwollaSebscriptionWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwolla:webhooks {id} {limit=25} {offset=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve webhooks from a dwolla webhook subscription';

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
        $limit = (int) $this->argument('limit');
        $offset = (int) $this->argument('offset');
        $webhook = $this->dwollaService->retrieveSubscriptionWebhook($id, $limit, $offset);
        $this->info(json_encode($webhook, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}