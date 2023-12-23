<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedMeetsFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_meets_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('meet_registration_id');
            $table->unsignedDecimal('fees', 12, 4);
            $table->unsignedDecimal('fess_in_percentage', 12, 4);
            $table->timestamps();

            $table->foreign('meet_registration_id')->references('id')->on('meet_registrations')
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
        Schema::dropIfExists('featured_meets_fees');
    }
}
