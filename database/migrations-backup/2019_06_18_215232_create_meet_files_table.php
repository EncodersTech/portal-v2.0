<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_id')->index();
            $table->string('name');
            $table->text('path');
            $table->text('description')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('meet_files');
    }
}
