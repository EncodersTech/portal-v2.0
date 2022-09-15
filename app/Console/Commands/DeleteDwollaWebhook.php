<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DwollaService;

class DeleteDwollaWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwolla:delete-webhook {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a dwolla webhook';

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
        $id = $this->argument('url');
        $webhook = $this->dwollaService->deleteWebhook($id);
        $this->info(json_encode($webhook, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}