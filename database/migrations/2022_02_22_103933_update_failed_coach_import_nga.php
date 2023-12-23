<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFailedCoachImportNga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('failed_coach_imports', function (Blueprint $table) {
            $table->string('nga_no')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('failed_coach_imports', 'nga_no')) {
            Schema::table('failed_coach_imports', function (Blueprint $table) {
                $table->dropColumn('nga_no');
            });
        }
    }
}
