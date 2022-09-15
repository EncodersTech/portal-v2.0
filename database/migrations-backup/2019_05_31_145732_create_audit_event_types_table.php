<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_event_types', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id')->index();
            $table->string('name');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('audit_event_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_event_types');
    }
}
