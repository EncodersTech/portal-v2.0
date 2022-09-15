<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TempMeetDeposit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->boolean('accept_deposit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('temporary_meets', 'accept_deposit')) {
            Schema::table('temporary_meets', function (Blueprint $table) {
                $table->dropColumn('accept_deposit');
            });
        }
    }
}
