<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Traits\Excludable;

class CategoryMeet extends Pivot
{
    use Excludable;
    
    protected $guarded = [];
    
    public const PIVOT_FIELDS = [
        'sanction_no', 'sanctioning_body_id', 'officially_sanctioned', 'frozen'
    ];

    public function requiresSanction() {
        return LevelCategory::requiresSanction($this->sanctioning_body_id); 
    }

    public function sanctioning_body() {
        return $this->belongsTo(SanctioningBody::class);
    }

    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }
}
