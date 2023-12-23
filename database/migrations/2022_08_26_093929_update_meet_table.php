<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meets', function (Blueprint $table) {
            $table->boolean('registration_first_discount_is_enable')->default(false);
            $table->boolean('registration_second_discount_is_enable')->default(false);
            $table->boolean('registration_third_discount_is_enable')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meets', function (Blueprint $table) {
            $table->dropColumn('registration_first_discount_is_enable');
            $table->dropColumn('registration_second_discount_is_enable');
            $table->dropColumn('registration_third_discount_is_enable');
        });
    }
}
