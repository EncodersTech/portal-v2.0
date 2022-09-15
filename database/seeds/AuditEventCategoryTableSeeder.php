<?php

use Illuminate\Database\Seeder;
use App\Models\AuditEventCategory;

class AuditEventCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_FUNDING_SOURCES,
            'name' => 'Funding Sources Management'
        ]);
        
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_MEMBER_MANAGEMENT,
            'name' => 'Member Management'
        ]);
        
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_GYM_MANAGEMENT,
            'name' => 'Gym Management'
        ]);
            
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_ROSTER_MANAGEMENT,
            'name' => 'Roster Management'
        ]);
        
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_MEET_MANAGEMENT,
            'name' => 'Meet Creation And Management'
        ]);
        
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_MEET_REGISTRATION,
            'name' => 'Meet Registration'
        ]);
        
        AuditEventCategory::create([
            'id' => AuditEventCategory::CATEGORY_EMAILING,
            'name' => 'Emailing'
        ]);
    }
}
