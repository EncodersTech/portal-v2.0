<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAthleteLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('athlete_levels', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('sanctioning_body_id')->index();
            $table->unsignedInteger('level_category_id')->index();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('abbreviation')->nullable();
            $table->boolean('pair')->default(false);
            $table->boolean('group')->default(false);
            $table->boolean('is_disabled')->default(false);

            $table->timestamps();

            $table->foreign('sanctioning_body_id')->references('id')->on('sanctioning_bodies')->onDelete('cascade');
            $table->foreign('level_category_id')->references('id')->on('level_categories')->onDelete('cascade');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('athlete_levels');
    }
}
