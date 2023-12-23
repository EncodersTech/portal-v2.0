<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberInvitation extends Model
{
    
    protected $fillable = [
        'email',
        'token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
