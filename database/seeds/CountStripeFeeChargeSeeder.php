<?php

use App\Models\MeetTransaction;
use Illuminate\Database\Seeder;
use Stripe\BalanceTransaction;
use Stripe\Charge;

class CountStripeFeeChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetTransactions = MeetTransaction::where('method', MeetTransaction::PAYMENT_METHOD_CC)->get();
        foreach ($meetTransactions as $meetTransaction) {
            $fee = 0;
            $charge = Charge::retrieve($meetTransaction->processor_id);
            $balance_charge = BalanceTransaction::retrieve($charge->balance_transaction);

            if ($balance_charge->fee > 0) {
                $fee = $balance_charge->fee / 100;
            }

            $meetTransaction->update(['processor_charge_fee' => $fee]);
        }
    }
}
