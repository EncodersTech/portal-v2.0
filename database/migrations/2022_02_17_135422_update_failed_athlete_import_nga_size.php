<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFailedAthleteImportNgaSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('failed_athlete_imports', function (Blueprint $table) {
            $table->string('tshirt_size_id')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('failed_athlete_imports', 'tshirt_size_id')) {
            Schema::table('failed_athlete_imports', function (Blueprint $table) {
                $table->dropColumn('tshirt_size_id');
            });
        }
    }
}
