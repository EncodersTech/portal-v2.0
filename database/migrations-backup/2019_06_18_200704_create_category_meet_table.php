<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_meet', function (Blueprint $table) {
            $table->unsignedInteger('sanctioning_body_id')->index();
            $table->unsignedInteger('level_category_id')->index();
            $table->unsignedBigInteger('meet_id')->index();
            $table->string('sanction_no')->nullable();
            $table->timestamps();

            $table->primary(['sanctioning_body_id', 'level_category_id', 'meet_id']);
            $table->foreign('sanctioning_body_id')->references('id')->on('sanctioning_bodies')->onDelete('cascade');
            $table->foreign('level_category_id')->references('id')->on('level_categories')->onDelete('cascade');
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
        Schema::dropIfExists('category_meets');
    }
}
