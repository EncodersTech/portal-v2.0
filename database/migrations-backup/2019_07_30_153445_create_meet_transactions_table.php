<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id')->index();
            $table->string('processor_id')->unique();

            $table->unsignedDecimal('handling_rate', 12, 4)->default(0);
            $table->unsignedDecimal('processor_rate', 12, 4)->default(0);
            $table->unsignedDecimal('total', 12, 4);
            $table->json('breakdown');
            $table->boolean('was_replaced')->default(false);
            $table->unsignedInteger('method')->index();
            $table->unsignedInteger('status')->index();

            $table->timestamps();

            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meet_transactions');
    }
}
