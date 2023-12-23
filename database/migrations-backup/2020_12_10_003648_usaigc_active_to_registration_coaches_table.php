<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsaigcActiveToRegistrationCoachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registration_coaches', function (Blueprint $table) {
            $table->boolean('usaigc_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registration_coaches', function (Blueprint $table) {
            if (Schema::hasColumn('usaigc_active')){
                $table->dropColumn('usaigc_active');
            }
        });
    }
}
