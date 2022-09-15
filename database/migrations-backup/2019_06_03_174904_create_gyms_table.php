<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGymsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gyms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name');
            $table->string('short_name');
            $table->string('profile_picture');
            $table->string('addr_1');
            $table->string('addr_2')->nullable();
            $table->string('city');
            $table->unsignedInteger('state_id')->index();
            $table->string('zipcode');
            $table->unsignedInteger('country_id')->index();
            $table->string('office_phone');
            $table->string('mobile_phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('usag_membership')->nullable()->default(null);
            $table->string('usaigc_membership')->nullable()->default(null);
            $table->string('aau_membership')->nullable()->default(null);
            $table->boolean('is_archived')->default(false);

            $table->unsignedDecimal('handling_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('cc_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('paypal_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('ach_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('check_fee_override', 12, 4)->nullable();
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gyms');
    }
}
