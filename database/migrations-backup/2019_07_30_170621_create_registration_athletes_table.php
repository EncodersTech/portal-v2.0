<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationAthletesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_athletes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id')->index();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();
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

            $table->boolean('was_late')->default(false);
            $table->boolean('in_waitlist')->default(false);

            $table->unsignedDecimal('fee', 12, 4)->default(0);
            $table->unsignedDecimal('late_fee', 12, 4)->default(0);
            $table->unsignedDecimal('refund', 12, 4)->default(0);
            $table->unsignedDecimal('late_refund', 12, 4)->default(0);
            
            $table->unsignedInteger('status')->index();

            $table->timestamps();            

            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')->onDelete('cascade');
            $table->foreign('level_registration_id')->references('id')->on('level_registration')->onDelete('cascade');
            $table->foreign('tshirt_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
            $table->foreign('leo_size_id')->references('id')->on('clothing_sizes')->onDelete('cascade');
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
        Schema::dropIfExists('registration_athletes');
    }
}
