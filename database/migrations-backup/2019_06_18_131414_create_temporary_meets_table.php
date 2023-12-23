<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemporaryMeetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_meets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->index();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('website')->nullable();
            $table->text('equipement')->nullable();
            $table->text('notes')->nullable();
            $table->text('special_annoucements')->nullable();

            $table->unsignedInteger('tshirt_size_chart_id')->nullable();
            $table->unsignedInteger('leo_size_chart_id')->nullable();

            $table->unsignedBigInteger('mso_meet_id')->nullable();
            
            $table->text('admissions')->nullable();

            $table->string('venue_name')->nullable();
            $table->string('venue_addr_1')->nullable();
            $table->string('venue_addr_2')->nullable();
            $table->string('venue_city')->nullable();
            $table->unsignedInteger('venue_state_id')->nullable();
            $table->string('venue_zipcode')->nullable();
            $table->string('venue_website')->nullable();

            $table->date('registration_start_date')->nullable();
            $table->date('registration_end_date')->nullable();
            $table->date('registration_scratch_end_date')->nullable();

            $table->boolean('allow_late_registration')->default(false);
            $table->unsignedDecimal('late_registration_fee', 12, 4)->default(0);
            $table->date('late_registration_start_date')->nullable();
            $table->date('late_registration_end_date')->nullable();

            $table->unsignedInteger('athlete_limit')->nullable();
            
            $table->boolean('accept_paypal')->default(false);
            $table->boolean('accept_ach')->default(false);
            $table->boolean('accept_mailed_check')->default(false);

            $table->text('mailed_check_instructions')->nullable();

            $table->boolean('defer_handling_fees')->default(false);
            $table->boolean('defer_processor_fees')->default(false);

            $table->boolean('process_refunds')->default(false);

            $table->text('categories')->nullable();
            $table->unsignedInteger('meet_competition_format_id')->nullable();
            $table->string('meet_competition_format_other')->nullable();
            $table->text('team_format')->nullable();
            $table->longText('levels')->nullable();

            $table->text('schedule')->nullable();
            $table->text('files')->nullable();

            $table->string('primary_contact_first_name')->nullable();
            $table->string('primary_contact_last_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->string('primary_contact_fax')->nullable();

            $table->string('secondary_contact')->default(false);
            $table->string('secondary_contact_first_name')->nullable();
            $table->string('secondary_contact_last_name')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('secondary_contact_job_title')->nullable();
            $table->string('secondary_contact_phone')->nullable();
            $table->string('secondary_contact_fax')->nullable();
            $table->string('secondary_cc')->default(false);

            $table->unsignedInteger('step')->default(1);
            
            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('tshirt_size_chart_id')->references('id')->on('clothing_size_charts')->onDelete('cascade');
            $table->foreign('leo_size_chart_id')->references('id')->on('clothing_size_charts')->onDelete('cascade');
            $table->foreign('venue_state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('meet_competition_format_id')->references('id')->on('meet_competition_formats')->onDelete('cascade');

            //$table->unique(['id', 'gym_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporary_meets');
    }
}
