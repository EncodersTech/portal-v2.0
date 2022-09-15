<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFailedAthleteImportNga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('failed_athlete_imports', function (Blueprint $table) {
            $table->string('nga_no')->index()->nullable();
            $table->unsignedInteger('nga_level_id')->nullable();
            $table->boolean('nga_active')->default(false);
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
        if (Schema::hasColumn('failed_athlete_imports', 'nga_no')) {
            Schema::table('failed_athlete_imports', function (Blueprint $table) {
                $table->dropColumn('nga_no');
            });
        }
        if (Schema::hasColumn('failed_athlete_imports', 'nga_level_id')) {
            Schema::table('failed_athlete_imports', function (Blueprint $table) {
                $table->dropColumn('nga_level_id');
            });
        }
        if (Schema::hasColumn('failed_athlete_imports', 'nga_active')) {
            Schema::table('failed_athlete_imports', function (Blueprint $table) {
                $table->dropColumn('nga_active');
            });
        }
        if (Schema::hasColumn('failed_athlete_imports', 'tshirt_size_id')) {
            Schema::table('failed_athlete_imports', function (Blueprint $table) {
                $table->dropColumn('tshirt_size_id');
            });
        }
    }
}
