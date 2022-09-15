<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class MeetAdmission extends Model
{
    use Excludable;
    
    protected $guarded = ['id'];
    
    public const TYPE_FREE = 1;
    public const TYPE_PAID = 2;
    public const TYPE_TBD = 3;

    public const TYPE_NAMES = [
        self::TYPE_FREE => 'Free',
        self::TYPE_PAID => 'Fee',
        self::TYPE_TBD => 'TBD',
    ];
    
    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }
}
