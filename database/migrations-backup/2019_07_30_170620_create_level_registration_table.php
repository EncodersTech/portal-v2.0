<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelRegistrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('level_registration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id')->index();
            $table->unsignedInteger('level_id')->index()->nullable();
            $table->boolean('allow_men');
            $table->boolean('allow_women');

            $table->unsignedDecimal('registration_fee', 12, 4)->default(0);
            $table->unsignedDecimal('late_registration_fee', 12, 4)->default(0);

            $table->boolean('allow_specialist');
            $table->unsignedDecimal('specialist_registration_fee', 12, 4)->default(0);
            $table->unsignedDecimal('specialist_late_registration_fee', 12, 4)->default(0);
            
            $table->boolean('allow_teams');
            $table->unsignedDecimal('team_registration_fee', 12, 4)->default(0);
            $table->unsignedDecimal('team_late_registration_fee', 12, 4)->default(0);

            $table->boolean('enable_athlete_limit')->default(false);
            $table->unsignedInteger('athlete_limit')->nullable();

            $table->boolean('has_team')->default(false);
            $table->boolean('was_late')->default(false);

            $table->unsignedDecimal('team_fee', 12, 4)->default(0);
            $table->unsignedDecimal('team_late_fee', 12, 4)->default(0);
            $table->unsignedDecimal('team_refund', 12, 4)->default(0);
            $table->unsignedDecimal('team_late_refund', 12, 4)->default(0);
            
            $table->timestamps();

            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_registration');
    }
}
