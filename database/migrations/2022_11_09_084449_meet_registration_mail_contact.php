<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MeetRegistrationMailContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meets', function (Blueprint $table) {
            $table->boolean('get_mail_primary')->default(true);
            $table->boolean('get_mail_secondary')->default(true);
        });
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->boolean('get_mail_primary')->default(true);
            $table->boolean('get_mail_secondary')->default(true);
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
            $table->dropColumn('get_mail_primary');
            $table->dropColumn('get_mail_secondary');
        });
        Schema::table('temporary_meets', function (Blueprint $table) {
            $table->dropColumn('get_mail_primary');
            $table->dropColumn('get_mail_secondary');
        });
    }
}
