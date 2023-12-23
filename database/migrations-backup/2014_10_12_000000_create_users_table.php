<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('is_admin')->default(false);

            $table->string('first_name');
            $table->string('last_name');
            $table->string('office_phone');
            $table->string('job_title');
            $table->string('profile_picture');
            $table->unsignedDecimal('cleared_balance', 12, 4)->default(0);
            $table->unsignedDecimal('pending_balance', 12, 4)->default(0);
            $table->string('stripe_customer_id')->unique()->nullable();
            $table->string('dwolla_customer_id')->unique()->nullable();

            $table->unsignedDecimal('handling_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('cc_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('paypal_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('ach_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('check_fee_override', 12, 4)->nullable();
                                    
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
        Schema::dropIfExists('users');
    }
}
