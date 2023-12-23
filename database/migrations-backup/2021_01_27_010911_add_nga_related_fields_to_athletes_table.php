<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNgaRelatedFieldsToAthletesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('athletes', function (Blueprint $table) {
            $table->string('nga_no')->after('aau_active')->index()->nullable();
            $table->unsignedInteger('nga_level_id')->after('nga_no')->nullable();
            $table->boolean('nga_active')->after('nga_level_id')->default(false);

            $table->foreign('nga_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');

            $table->unique(['gym_id', 'nga_no']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('athletes', function (Blueprint $table) {
            //
        });
    }
}
