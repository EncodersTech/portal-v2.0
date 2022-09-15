<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAthleteSpecialistEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athlete_specialist_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('sanctioning_body_id')->index();
            $table->string('name');
            $table->string('abbreviation');
            $table->boolean('male')->default(true);
            $table->boolean('female')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athlete_specialist_events');
    }
}
