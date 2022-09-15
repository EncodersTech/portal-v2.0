<?php

namespace App\Console\Commands;

use App\Exceptions\CustomBaseException;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\StripeService;
use App\Exceptions\CustomStripeException;
use App\Exceptions\CustomDwollaException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\DwollaService;
use App\Services\DwollaScheduleWithdrawal;
use App\Helper;
use App\Models\Gym;
use App\Models\DwollaVerificationAttempt;
use App\Models\Setting;
use App\Models\MemberUser;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class WithdrawDwollaBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdraw-dwolla-balance:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiating Dwolla Scheduled withdraw balance';

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
        // Log::info('transaction user id ');
        try {
            $is_schedule_withdraw_enabled = Setting::where('key','is_schedule_withdraw_enabled')->first();
            $auto_withdraw_charge = Setting::where('key','auto_withdraw_charge')->first();

            if($is_schedule_withdraw_enabled->value)
            {
                $data = DB::table('withdraw_scheduler')
                ->where('is_active', true)
                ->get();
        
                foreach ($data as $k) {
                    // print_r($k->user_id);
                    if($k->last_attempt == null)
                        $k->last_attempt = $k->created_at;
                    
                    $date1 = new \DateTime($k->last_attempt);
                    $date2 = new \DateTime(now());
                    $interval = $date1->diff($date2);

                    $dwollaScheduleWithdrawal = resolve(DwollaScheduleWithdrawal::class); /** @var DwollaScheduleWithdrawal $dwollaScheduleWithdrawal */
                    $user = User::find($k->user_id);
                    if($k->frequency == 1)// && $interval->d >= 7)
                    {
                        DB::table('withdraw_scheduler')
                            ->where('id', $k->id)
                            ->update(['last_attempt' => now() , 'attempt' => $k->attempt + 1, 'updated_at' => now()]);
        
                        $dwollaScheduleWithdrawal->withdrawBalanceSchedule($user, (array) $k, $auto_withdraw_charge->value);
                    }
                    else if($k->frequency == 2 && $interval->d >= 14)
                    {
                        DB::table('withdraw_scheduler')
                        ->where('id', $k->id)
                        ->update(['last_attempt' => now() , 'attempt' => $k->attempt + 1, 'updated_at' => now()]);
    
                        $dwollaScheduleWithdrawal->withdrawBalanceSchedule($user, (array) $k, $auto_withdraw_charge->value);
                    }
                    else if($k->frequency == 3 && $interval->d >= 30)
                    {
                        DB::table('withdraw_scheduler')
                        ->where('id', $k->id)
                        ->update(['last_attempt' => now() , 'attempt' => $k->attempt + 1, 'updated_at' => now()]);
    
                        $dwollaScheduleWithdrawal->withdrawBalanceSchedule($user, (array) $k, $auto_withdraw_charge->value);
                    }
        
                }
            }
            $this->info('Cleared');
        } catch(\Throwable $e) {
            $this->error(self::class . ' failed : ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
