<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrationSpecialistEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_specialist_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('specialist_id')->index();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();

            $table->boolean('was_late')->default(false);
            $table->boolean('in_waitlist')->default(false);

            $table->unsignedDecimal('fee', 12, 4)->default(0);
            $table->unsignedDecimal('late_fee', 12, 4)->default(0);
            $table->unsignedDecimal('refund', 12, 4)->default(0);
            $table->unsignedDecimal('late_refund', 12, 4)->default(0);
            
            $table->unsignedInteger('status')->index();

            $table->timestamps();

            $table->foreign('specialist_id')->references('id')->on('registration_specialists')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('athlete_specialist_events')->onDelete('cascade');
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
        Schema::dropIfExists('registration_specialist_events');
    }
}
