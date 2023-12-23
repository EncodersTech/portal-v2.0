<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class Country extends Model
{
    use Excludable;
    
    public function gyms()
    {
        return $this->hasMany(Gym::class);
    }
}
