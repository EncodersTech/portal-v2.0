<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DwollaService;
use DwollaSwagger\models\WebhookListResponse;

class ListDwollaWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwolla:webhook-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists Dwolla webhooks subscriptions';

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
        $webhook = $this->dwollaService->listWebhook();
        $this->info(json_encode($webhook->_embedded->{'webhook-subscriptions'}, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}