<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueConstraintToGymMemberships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->unique('usag_membership');
            $table->unique('usaigc_membership');
            $table->unique('aau_membership');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropUnique('usag_membership');
            $table->dropUnique('usaigc_membership');
            $table->dropUnique('aau_membership');
        });
    }
}
