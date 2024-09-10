<?php

namespace App\Console\Commands;

use App\Exceptions\CustomBaseException;
use App\Models\User;
use App\Models\UserBalanceTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearPendingBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears pending users\' balance';

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
            $this->info('clear pending revenue balance start...');
            $now = now()->addHours(12); /** @var Carbon $now */
            UserBalanceTransaction::where('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_REGISTRATION_REVENUE)
                ->orWhere('type', UserBalanceTransaction::BALANCE_TRANSACTION_TYPE_TICKET)
                ->where('status', UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_PENDING)
                ->where('clears_on', '<=', $now) //uncomment this before pushing
                ->chunkById(100, function ($transactions) {
                    foreach ($transactions as $tx) { /** @var UserBalanceTransaction $tx */
                        DB::beginTransaction();
                        try {
                            Log::info('transaction user id ', [$tx->user_id]);
                            $user = User::lockForUpdate()->find($tx->user_id); /** @var User $user */
                            if ($user == null)
                                throw new CustomBaseException('No such user with id `' . $tx->user_id . '`');

                            $tx->status = UserBalanceTransaction::BALANCE_TRANSACTION_STATUS_CLEARED;
                            $user->cleared_balance += $tx->total;
                            $tx->save();
                            $user->save();

                            DB::commit();
                        } catch(\Throwable $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }
                });
            $this->info('clear pending revenue balance end...');
        } catch(\Throwable $e) {
            $this->error(self::class . ' failed : ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
