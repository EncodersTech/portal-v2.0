<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClothingSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clothing_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('clothing_size_chart_id');
            $table->string('size');
            $table->boolean('is_disabled')->default(false);
            $table->timestamps();

            $table->foreign('clothing_size_chart_id')->references('id')->on('clothing_size_charts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clothing_sizes');
    }
}
