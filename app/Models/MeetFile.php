<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetFile extends Model
{
    protected $guarded = ['id'];
    
    public function meet()
    {
        return $this->belongsTo(Meet::class);
    }
}
