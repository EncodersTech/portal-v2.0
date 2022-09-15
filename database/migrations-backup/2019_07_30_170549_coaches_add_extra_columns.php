<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoachesAddExtraColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coaches', function (Blueprint $table) {
            $table->date('usag_expiry')->nullable();
            $table->date('usag_safety_expiry')->nullable();
            $table->date('usag_safesport_expiry')->nullable();
            $table->date('usag_background_expiry')->nullable();
            $table->boolean('usag_u100_certification')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coaches', function (Blueprint $table) {
            if (Schema::hasColumn('usag_expiry'))
                $table->dropColumn('usag_expiry');
            
            if (Schema::hasColumn('usag_safety_expiry'))
                $table->dropColumn('usag_safety_expiry');

            if (Schema::hasColumn('usag_safesport_expiry'))
                $table->dropColumn('usag_safesport_expiry');
            
            if (Schema::hasColumn('usag_background_expiry'))
                $table->dropColumn('usag_background_expiry');

            if (Schema::hasColumn('usag_u100_certification'))
                $table->dropColumn('usag_u100_certification');
            
        });
    }
}
