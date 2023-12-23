<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationAthleteVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_athlete_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id')->index();
            $table->unsignedInteger('sanctioning_body_id')->index();
            
            $table->json('athletes');
            $table->json('results')->default('{}');
            
            $table->unsignedInteger('status')->index();
            $table->timestamps();

            $table->unique(['meet_registration_id', 'sanctioning_body_id']);
            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')->onDelete('cascade');
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
        Schema::dropIfExists('registration_athlete_verifications');
    }
}
