<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MemberUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('member_id');

            $table->boolean('can_manage_gyms')->default(false);
            $table->boolean('can_manage_rosters')->default(false);
            $table->boolean('can_create_meet')->default(false);
            $table->boolean('can_edit_meet')->default(false);
            $table->boolean('can_register_in_meet')->default(false);
            $table->boolean('can_email_participant')->default(false);
            $table->boolean('can_email_host')->default(false);
            $table->boolean('can_access_reports')->default(false);
            $table->boolean('should_be_cced')->default(false);

            $table->timestamps();

            $table->primary(['user_id', 'member_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_user');
    }
}
