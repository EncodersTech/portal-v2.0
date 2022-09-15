<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsagSanctionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usag_sanctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number')->index();
            $table->unsignedBigInteger('gym_id')->nullable()->index();
            $table->unsignedBigInteger('gym_usag_no')->index();            
            $table->unsignedBigInteger('meet_id')->nullable()->index()->default(null);
            $table->unsignedInteger('level_category_id')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index()->default(null);

            $table->unsignedInteger('action')->index();

            $table->json('payload');

            $table->unsignedInteger('status')->index();
            $table->timestamp('timestamp');

            $table->unsignedInteger('notification_stage');
            $table->timestamp('next_notification_on');

            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('usag_meet_name')->nullable();

            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('meet_id')->references('id')->on('meets')->onDelete('cascade');
            $table->foreign('level_category_id')->references('id')->on('level_categories')->onDelete('cascade');
        });
     
        Schema::table('usag_sanctions', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('usag_sanctions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usag_sanctions');
    }
}
