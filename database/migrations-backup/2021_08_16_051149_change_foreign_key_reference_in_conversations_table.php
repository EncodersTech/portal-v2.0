<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeForeignKeyReferenceInConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign('conversations_from_id_foreign');
            $table->dropForeign('conversations_to_id_foreign');

            $table->foreign('from_id')->references('id')->on('gyms')
                ->onDelete('cascade');
            $table->foreign('to_id')->references('id')->on('gyms')
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
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign('conversations_from_id_foreign');
            $table->dropForeign('conversations_to_id_foreign');

            $table->foreign('from_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('to_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }
}
