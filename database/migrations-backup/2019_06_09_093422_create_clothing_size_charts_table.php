<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClothingSizeChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clothing_size_charts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('is_leo');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_disabled')->default(false);
            
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
        Schema::dropIfExists('clothing_size_charts');
    }
}
