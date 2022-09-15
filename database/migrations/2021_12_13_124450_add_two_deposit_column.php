<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoDepositColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_transactions', function (Blueprint $table) {
            $table->boolean('is_deposit')->default(0);
            $table->boolean('is_deposit_sattle')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('meet_transactions', 'is_deposit')) {
            Schema::table('meet_transactions', function (Blueprint $table) {
                $table->dropColumn('is_deposit');
            });
        }
        if (Schema::hasColumn('meet_transactions', 'is_deposit_sattle')) {
            Schema::table('meet_transactions', function (Blueprint $table) {
                $table->dropColumn('is_deposit_sattle');
            });
        }
    }
}
