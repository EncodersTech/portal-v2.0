<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsagReservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usag_reservations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->nullable()->index();
            $table->unsignedBigInteger('gym_usag_no')->index();
            $table->unsignedBigInteger('usag_sanction_id')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index()->default(null);

            $table->unsignedInteger('action')->index();

            $table->json('payload');

            $table->unsignedInteger('status')->index();
            $table->timestamp('timestamp');

            $table->string('contact_name');
            $table->string('contact_email');

            $table->unsignedInteger('notification_stage');
            $table->timestamp('next_notification_on');

            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('usag_sanction_id')->references('id')->on('usag_sanctions')->onDelete('cascade');
        });
     
        Schema::table('usag_reservations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('usag_reservations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usag_reservations');
    }
}
