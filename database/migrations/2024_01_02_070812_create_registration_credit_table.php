<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationCreditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_credit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id');
            $table->unsignedBigInteger('gym_id');
            $table->unsignedBigInteger('meet_id');
            $table->unsignedBigInteger('credit_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration_credit');
    }
}
