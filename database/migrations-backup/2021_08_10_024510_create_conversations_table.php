<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message');
            $table->integer('from_id');
            $table->integer('to_id');
            $table->boolean('is_host')->default(false);
            $table->unsignedBigInteger('gym_id')->nullable();
            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')
                ->onDelete('cascade');

            $table->foreign('from_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('to_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
