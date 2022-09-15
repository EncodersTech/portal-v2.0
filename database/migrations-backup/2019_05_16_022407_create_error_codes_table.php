<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('error_code_category_id');
            $table->integer('code')->unique();
            $table->text('description');
            $table->timestamps();

            $table->foreign('error_code_category_id')
                ->references('id')
                ->on('error_code_categories')
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
        Schema::dropIfExists('error_codes');
    }
}
