<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetSubscription extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'meet_id',
        'user_id'
    ];

    public function meet()
    {
        return $this->belongsTo(Meet::class,'meet_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
