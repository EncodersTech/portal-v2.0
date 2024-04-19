<?php

namespace App\Console\Commands;

use App\Exceptions\CustomBaseException;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\USAGReservation;

class USAGReservationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usag-reservation-reminder:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send USAG reservation reminder to users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->info('Sending USAG reservation reminder start...');
            
            USAGReservation::pendingReservations();

            $this->info('Sending USAG reservation reminder end...');
        } catch(\Throwable $e) {
            $this->error(self::class . ' failed : ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
