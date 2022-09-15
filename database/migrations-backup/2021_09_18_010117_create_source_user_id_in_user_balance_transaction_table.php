<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourceUserIdInUserBalanceTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_balance_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('source_user_id')->nullable();
            $table->unsignedBigInteger('destination_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('user_balance_transactions', 'source_user_id')) {
            Schema::table('user_balance_transactions', function (Blueprint $table) {
                $table->dropColumn('source_user_id');
            });
        }
        if (Schema::hasColumn('user_balance_transactions', 'destination_user_id')) {
            Schema::table('user_balance_transactions', function (Blueprint $table) {
                $table->dropColumn('destination_user_id');
            });
        }
    }
}
