<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationCoachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_coaches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id')->index();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob');

            $table->unsignedInteger('tshirt_size_id')->nullable();

            $table->string('usag_no')->index()->nullable();
            $table->boolean('usag_active')->default(false);
            $table->date('usag_expiry')->nullable();
            $table->date('usag_safety_expiry')->nullable();
            $table->date('usag_safesport_expiry')->nullable();
            $table->date('usag_background_expiry')->nullable();            
            $table->boolean('usag_u100_certification')->default(false);

            $table->string('usaigc_no')->index()->nullable();
            $table->boolean('usaigc_background_check')->default(false);

            $table->string('aau_no')->index()->nullable();

            $table->boolean('was_late')->default(false);
            $table->boolean('in_waitlist')->default(false);

            $table->unsignedInteger('status')->index();

            $table->timestamps();

            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')->onDelete('cascade');
            $table->foreign('tshirt_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('meet_transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration_coaches');
    }
}
