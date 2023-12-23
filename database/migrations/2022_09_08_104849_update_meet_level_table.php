<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMeetLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('level_meet', function (Blueprint $table) {
            $table->unsignedDecimal('registration_fee_first', 12, 4)->default(0);
            $table->unsignedDecimal('registration_fee_second', 12, 4)->default(0);
            $table->unsignedDecimal('registration_fee_third', 12, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('level_meet', function (Blueprint $table) {
            $table->dropColumn('registration_fee_first');
            $table->dropColumn('registration_fee_second');
            $table->dropColumn('registration_fee_third');
        });
    }
}
