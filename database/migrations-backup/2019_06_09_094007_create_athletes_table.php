<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAthletesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athletes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob');
            $table->boolean('is_us_citizen');

            $table->string('parent_id')->nullable();
            $table->string('parent_first_name')->nullable();
            $table->string('parent_last_name')->nullable();

            $table->unsignedInteger('tshirt_size_id')->nullable();
            $table->unsignedInteger('leo_size_id')->nullable();

            $table->string('usag_no')->index()->nullable();
            $table->unsignedInteger('usag_level_id')->nullable();
            $table->boolean('usag_active')->default(false);

            $table->string('usaigc_no')->index()->nullable();
            $table->unsignedInteger('usaigc_level_id')->nullable();
            $table->boolean('usaigc_active')->default(false);

            $table->string('aau_no')->index()->nullable();
            $table->unsignedInteger('aau_level_id')->nullable();
            $table->boolean('aau_active')->default(false);

            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('tshirt_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
            $table->foreign('leo_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
            $table->foreign('usag_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
            $table->foreign('usaigc_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');
            $table->foreign('aau_level_id')->references('id')->on('athlete_levels')->onDelete('cascade');

            $table->unique(['gym_id', 'usag_no']);
            $table->unique(['gym_id', 'usaigc_no']);
            $table->unique(['gym_id', 'aau_no']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athletes');
    }
}
