<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditEventCategory extends Model
{
    public const CATEGORY_FUNDING_SOURCES = 1;
    public const CATEGORY_MEMBER_MANAGEMENT = 2;
    public const CATEGORY_GYM_MANAGEMENT = 3;
    public const CATEGORY_ROSTER_MANAGEMENT = 4;
    public const CATEGORY_MEET_MANAGEMENT = 5;
    public const CATEGORY_MEET_REGISTRATION = 6;
    public const CATEGORY_EMAILING = 7;


    public function types()
    {
        return $this->hasMany(AuditEventType::class, 'category_id');
    }
}
