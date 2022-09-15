<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedCoachImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_coach_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob');

            $table->unsignedInteger('tshirt_size_id')->nullable();
            
            $table->string('usag_no')->nullable();
            $table->boolean('usag_active')->default(false);

            $table->string('usaigc_no')->nullable();
            $table->boolean('usaigc_background_check')->default(false);

            $table->string('aau_no')->nullable();

            $table->unsignedInteger('method');
            $table->unsignedInteger('sanctioning_body_id')->index();

            $table->text('raw');
            $table->integer('error_code')->index();
            $table->text('error_message');

            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('tshirt_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
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
        Schema::dropIfExists('failed_coach_imports');
    }
}
