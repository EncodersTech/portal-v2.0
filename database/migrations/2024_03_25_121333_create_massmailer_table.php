<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMassmailerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('massmailer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('host')->index();
            $table->unsignedInteger('meet_id')->index();
            $table->string('registered_gyms');
            $table->string('subject');
            $table->string('message')->nullable();
            $table->string('attachments')->nullable();
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
        Schema::dropIfExists('massmailer');
    }
}
