<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class State extends Model
{
    use Excludable;
    
    public function gyms()
    {
        return $this->hasMany(Gym::class);
    }

    public function inCountry(Country $country) {
        return (($this->code == 'WW') xor ($country->code == 'US'));
    }
}
