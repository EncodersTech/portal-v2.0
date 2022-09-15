<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorCode extends Model
{
    

    public function category()
    {
        return $this->belongsTo(ErrorCodeCategory::class);
    }
}
