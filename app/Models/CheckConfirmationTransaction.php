<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;

class CheckConfirmationTransaction extends Model
{
    use Excludable;

    protected $guarded = ['id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function transaction()
    {
        return $this->belongsTo(MeetTransaction::class, 'transaction_id');
    }
}
