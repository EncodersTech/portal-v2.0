<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'type',
        'user_id',
        'read_at',
        'meta',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
