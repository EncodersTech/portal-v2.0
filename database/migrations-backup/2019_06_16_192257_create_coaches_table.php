<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob');

            $table->unsignedInteger('tshirt_size_id')->nullable();
            
            $table->string('usag_no')->index()->nullable();
            $table->boolean('usag_active')->default(false);

            $table->string('usaigc_no')->index()->nullable();
            $table->boolean('usaigc_background_check')->default(false);

            $table->string('aau_no')->index()->nullable();

            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('tshirt_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
            
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
        Schema::dropIfExists('coaches');
    }
}
