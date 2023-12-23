<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedAthleteImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_athlete_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->boolean('is_us_citizen')->default(false);

            $table->string('parent_id')->nullable();
            $table->string('parent_first_name')->nullable();
            $table->string('parent_last_name')->nullable();

            $table->string('usag_no')->index()->nullable();
            $table->unsignedInteger('usag_level_id')->nullable();
            $table->boolean('usag_active')->default(false);

            $table->string('usaigc_no')->index()->nullable();
            $table->unsignedInteger('usaigc_level_id')->nullable();
            $table->boolean('usaigc_active')->default(false);

            $table->string('aau_no')->index()->nullable();
            $table->unsignedInteger('aau_level_id')->nullable();
            $table->boolean('aau_active')->default(false);

            $table->unsignedInteger('method');
            $table->unsignedInteger('sanctioning_body_id')->index();

            $table->text('raw');
            $table->integer('error_code');
            $table->text('error_message');

            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('usag_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
            $table->foreign('usaigc_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
            $table->foreign('aau_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
            $table->foreign('sanctioning_body_id')->references('id')->on('sanctioning_bodies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_athlete_imports');
    }
}
