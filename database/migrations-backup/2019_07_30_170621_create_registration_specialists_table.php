<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationSpecialistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_specialists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id')->index();
            $table->unsignedInteger('level_registration_id')->index();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob');
            $table->boolean('is_us_citizen');

            $table->unsignedInteger('tshirt_size_id')->nullable();
            $table->unsignedInteger('leo_size_id')->nullable();

            $table->string('usag_no')->index()->nullable();
            $table->boolean('usag_active')->default(false);

            $table->string('usaigc_no')->index()->nullable();
            $table->boolean('usaigc_active')->default(false);

            $table->string('aau_no')->index()->nullable();
            $table->boolean('aau_active')->default(false);
            
            $table->timestamps();            

            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')->onDelete('cascade');
            $table->foreign('level_registration_id')->references('id')->on('level_registration')->onDelete('cascade');
            $table->foreign('tshirt_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
            $table->foreign('leo_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration_specialists');
    }
}
