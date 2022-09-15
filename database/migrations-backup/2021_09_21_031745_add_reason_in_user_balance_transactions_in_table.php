<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonInUserBalanceTransactionsInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_balance_transactions', function (Blueprint $table) {
            $table->string('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('user_balance_transactions', 'reason')) {
            Schema::table('user_balance_transactions', function (Blueprint $table) {
                $table->dropColumn('reason');
            });
        }
    }
}
