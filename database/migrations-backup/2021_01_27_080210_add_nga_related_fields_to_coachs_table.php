<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNgaRelatedFieldsToCoachsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coaches', function (Blueprint $table) {
            $table->string('nga_no')->index()->nullable();

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
        Schema::table('coaches', function (Blueprint $table) {
            //
        });
    }
}
