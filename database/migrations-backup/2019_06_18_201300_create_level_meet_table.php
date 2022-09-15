<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('level_meet', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('athlete_level_id')->index();
            $table->unsignedBigInteger('meet_id')->index();
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
            
            $table->timestamps();

            $table->foreign('athlete_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
            $table->foreign('meet_id')->references('id')->on('meets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_meets');
    }
}
