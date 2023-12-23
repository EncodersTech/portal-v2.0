<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Meet;

class CreateMeetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gym_id')->index();
            $table->string('profile_picture');
            $table->string('name');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('website');
            $table->text('equipement');
            $table->text('notes')->nullable();
            $table->text('special_annoucements')->nullable();

            $table->unsignedInteger('tshirt_size_chart_id')->nullable();
            $table->unsignedInteger('leo_size_chart_id')->nullable();

            $table->unsignedBigInteger('mso_meet_id')->nullable();
            
            $table->string('venue_name');
            $table->string('venue_addr_1');
            $table->string('venue_addr_2')->nullable();
            $table->string('venue_city');
            $table->unsignedInteger('venue_state_id');
            $table->string('venue_zipcode');
            $table->string('venue_website')->nullable();

            $table->date('registration_start_date');
            $table->date('registration_end_date');
            $table->date('registration_scratch_end_date');

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

            $table->unsignedInteger('meet_competition_format_id');
            $table->string('meet_competition_format_other')->nullable();
            $table->text('team_format')->nullable();
            $table->boolean('use_usag_sanctions')->default(false);

            $table->string('schedule')->nullable();

            $table->string('primary_contact_first_name');
            $table->string('primary_contact_last_name');
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone');
            $table->string('primary_contact_fax')->nullable();

            $table->boolean('secondary_contact')->default(false);
            $table->string('secondary_contact_first_name')->nullable();
            $table->string('secondary_contact_last_name')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('secondary_contact_job_title')->nullable();
            $table->string('secondary_contact_phone')->nullable();
            $table->string('secondary_contact_fax')->nullable();
            $table->boolean('secondary_cc')->default(false);           

            $table->integer('is_published')->default(false);
            $table->integer('is_archived')->default(false);
            $table->integer('is_featured')->default(false);

            $table->unsignedDecimal('handling_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('cc_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('paypal_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('ach_fee_override', 12, 4)->nullable();
            $table->unsignedDecimal('check_fee_override', 12, 4)->nullable();
            
            $table->timestamps();

            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');
            $table->foreign('tshirt_size_chart_id')->references('id')->on('clothing_size_charts')->onDelete('cascade');
            $table->foreign('leo_size_chart_id')->references('id')->on('clothing_size_charts')->onDelete('cascade');
            $table->foreign('venue_state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('meet_competition_format_id')->references('id')->on('meet_competition_formats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meets');
    }
}
