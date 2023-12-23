<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('performed_by')->nullable()->index();
            $table->unsignedBigInteger('on_behalf_of')->index();
            $table->unsignedInteger('type_id')->index();
            $table->unsignedBigInteger('object_id')->nullable()->index();
            $table->unsignedInteger('param_1')->nullable()->index();
            $table->unsignedInteger('param_2')->nullable()->index();
            $table->unsignedInteger('param_3')->nullable()->index();
            $table->string('param_4')->nullable()->index();
            $table->string('param_5')->nullable()->index();
            $table->string('param_6')->nullable()->index();
            $table->json('event_meta')->nullable();
            $table->timestamps();

            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('on_behalf_of')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('audit_event_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_events');
    }
}
