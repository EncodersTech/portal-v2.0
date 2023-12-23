<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class ClothingSize extends Model
{
    use Excludable;
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    public function chart()
    {
        return $this->belongsTo(ClothingSizeChart::class, 'clothing_size_chart_id');
    }
}
