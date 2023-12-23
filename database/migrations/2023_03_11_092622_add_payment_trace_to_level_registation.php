<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentTraceToLevelRegistation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('level_registration', function (Blueprint $table) {
            $table->boolean('is_waitlist_team_paid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('level_registration', function (Blueprint $table) {
            $table->dropColumn('is_waitlist_team_paid');
        });
    }
}
