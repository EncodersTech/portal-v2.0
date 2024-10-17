<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('host_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_gym')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('host_tickets', 'customer_gym')) {
            Schema::table('host_tickets', function (Blueprint $table) {
                $table->dropColumn('customer_gym');
            });
        }
    }
}
