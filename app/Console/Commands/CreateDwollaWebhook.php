<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DwollaService;

class CreateDwollaWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwolla:create-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a dwolla webhook';

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
        $url = route('webhook.dwolla');

        if (config('app.debug'))
            $url = config('app.tunnel') . route('webhook.dwolla', [], false);

        $webhook = $this->dwollaService->createWebhook(
            $url,
            config('services.dwolla.webhook_secret')
        );
        $this->info($webhook);
    }
}
