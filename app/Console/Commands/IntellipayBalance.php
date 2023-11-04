<?php

namespace App\Console\Commands;

use App\Exceptions\CustomBaseException;
use App\Models\User;
use App\Models\IntellipayModel;
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

class IntellipayBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleare-onetimeach:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiating One Time ACH Balance Clear';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $this->info('Initiating One Time ACH Balance Clear Process');
        $intellipayModel = resolve(IntellipayModel::class);
        try{
            $intellipayModel->clear_payment();
            $this->info('Cleared');
        }
        catch(\Exception $e)
        {
            $this->error(self::class . ' failed : ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
?>