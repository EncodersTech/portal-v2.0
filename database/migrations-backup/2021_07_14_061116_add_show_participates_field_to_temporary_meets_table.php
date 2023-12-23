<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowParticipatesFieldToTemporaryMeetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->boolean('show_participate_clubs')->default(0);
        });

        Schema::table('meets', function (Blueprint $table) {
            $table->boolean('show_participate_clubs')->default(0);
        });
    }
}
