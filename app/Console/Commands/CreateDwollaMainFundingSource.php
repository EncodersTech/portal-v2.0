<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DwollaService;

class CreateDwollaMainFundingSource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwolla:master-source {account} {routing} {type} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a dwolla funding source in the mater account';

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
        $accountNumber = $this->argument('account');
        $routingNumber = $this->argument('routing');
        $bankAccountType = $this->argument('type');
        $name = $this->argument('name');

        $fs = $this->dwollaService->createMasterFundingSource(
            $accountNumber,
            $routingNumber,
            $bankAccountType,
            $name
        );

        $this->info($fs);
    }
}