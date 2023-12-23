<?php

use App\Exceptions\CustomBaseException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeOnBehalfOfNullableInAuditEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audit_events', function (Blueprint $table) {
            DB::statement('ALTER TABLE audit_events ALTER COLUMN on_behalf_of DROP NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audit_events', function (Blueprint $table) {
            throw new CustomBaseException('Unreversible migration. Manual changes required.');
        });
    }
}
