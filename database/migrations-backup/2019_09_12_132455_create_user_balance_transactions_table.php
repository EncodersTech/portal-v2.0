<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBalanceTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_balance_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('related_id')->nullable()->index();
            $table->string('related_type')->nullable()->index();

            $table->string('processor_id')->nullable()->unique();
            $table->unsignedDecimal('total', 12, 4);
            $table->string('description');
            $table->timestamp('clears_on')->index();
            $table->unsignedInteger('type')->index();
            $table->unsignedInteger('status')->index();            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_balance_transactions');
    }
}
