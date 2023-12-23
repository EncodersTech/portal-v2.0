<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDepositRatioTemp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->unsignedInteger('deposit_ratio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('temporary_meets', 'deposit_ratio')) {
            Schema::table('temporary_meets', function (Blueprint $table) {
                $table->dropColumn('deposit_ratio');
            });
        }
    }
}
