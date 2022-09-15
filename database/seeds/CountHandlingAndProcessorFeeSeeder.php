<?php

use App\Models\MeetTransaction;
use Illuminate\Database\Seeder;

class CountHandlingAndProcessorFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetTransactions = MeetTransaction::all();
        foreach ($meetTransactions as $meetTransaction) {
            if ($meetTransaction && isset($meetTransaction->breakdown['gym'])) {
                MeetTransaction::where('id', $meetTransaction->id)->update([
                    'handling_fee' => $meetTransaction->breakdown['gym']['handling'],
                    'processor_fee' => $meetTransaction->breakdown['gym']['processor'],
                ]);
            }
        }
    }
}
