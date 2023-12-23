<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTemporaryMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->date('registration_first_discount_end_date')->nullable();
            $table->date('registration_second_discount_end_date')->nullable();
            $table->date('registration_third_discount_end_date')->nullable();
            
            $table->double('registration_first_discount_amount')->nullable();
            $table->double('registration_second_discount_amount')->nullable();
            $table->double('registration_third_discount_amount')->nullable();

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
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->dropColumn('registration_first_discount_end_date');
            $table->dropColumn('registration_second_discount_end_date');
            $table->dropColumn('registration_third_discount_end_date');

            $table->dropColumn('registration_first_discount_amount');
            $table->dropColumn('registration_second_discount_amount');
            $table->dropColumn('registration_third_discount_amount');

            $table->dropColumn('registration_first_discount_is_enable');
            $table->dropColumn('registration_second_discount_is_enable');
            $table->dropColumn('registration_third_discount_is_enable');
        });
    }
}
