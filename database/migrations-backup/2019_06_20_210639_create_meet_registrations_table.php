<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('gym_id')->index();
            $table->unsignedBigInteger('meet_id')->index();
            
            $table->boolean('was_late')->default(false);

            $table->unsignedDecimal('late_fee', 12, 4)->default(0);
            $table->unsignedDecimal('late_refund', 12, 4)->default(0);

            $table->unsignedDecimal('handling_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('cc_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('paypal_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('ach_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('check_fee_override', 12, 4)->nullable();

            $table->unsignedInteger('status');

            $table->timestamps();

            //$table->unique(['gym_id', 'meet_id']);
            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
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
        Schema::dropIfExists('meet_registrations');
    }
}
