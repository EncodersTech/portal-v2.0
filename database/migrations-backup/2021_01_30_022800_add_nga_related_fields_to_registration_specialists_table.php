<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNgaRelatedFieldsToRegistrationSpecialistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registration_specialists', function (Blueprint $table) {
            $table->string('nga_no')->after('aau_no')->index()->nullable();
            $table->boolean('nga_active')->after('nga_no')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registration_specialists', function (Blueprint $table) {
            $table->dropColumn(['nga_no','nga_active']);
        });
    }
}
