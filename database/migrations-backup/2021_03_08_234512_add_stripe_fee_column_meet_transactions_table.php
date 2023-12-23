<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStripeFeeColumnMeetTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_transactions', function (Blueprint $table) {
            $table->unsignedDecimal('handling_fee', 12, 4)->default(0);
            $table->unsignedDecimal('processor_fee', 12, 4)->default(0);
            $table->unsignedDecimal('processor_charge_fee', 12, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_transactions', function (Blueprint $table) {
            $table->dropColumn(['processor_charge_fee', 'handling_fee', 'processor_fee']);
        });
    }
}
