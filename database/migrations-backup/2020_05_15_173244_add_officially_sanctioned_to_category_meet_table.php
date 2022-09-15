<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOfficiallySanctionedToCategoryMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category_meet', function (Blueprint $table) {
            $table->boolean('officially_sanctioned')->default(false);
            $table->boolean('frozen')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category_meet', function (Blueprint $table) {
            $table->dropColumn('frozen');
            $table->dropColumn('officially_sanctioned');
        });
    }
}
