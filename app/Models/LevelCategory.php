<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class LevelCategory extends Model
{
    use Excludable;
    
    public const GYMNASTICS_WOMEN = 1;
    public const GYMNASTICS_MEN = 2;
    public const TRAMPOLINE_TUMBLING = 3;
    public const RHYTHMIC = 4;
    public const ACROBATIC = 5;
    public const GYMNASTICS_FOR_ALL = 6;
    public const TUMBLING = 7;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];   
    
    public static function requiresSanction(int $body) {
        return in_array($body, [
            SanctioningBody::USAG,
        ]); 
    }
    
    public function levels()
    {
        return $this->hasMany(AthleteLevel::class);
    }    
}
