<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class ClothingSizeChart extends Model
{
    use Excludable;
    
    public const CHART_DEFAULT_TSHIRT = 1;
    public const CHART_DEFAULT_LEO = 2;
    public const CHART_LEO_GK = 3;
    public const CHART_LEO_ALPHA_FACTOR = 4;
    public const CHART_LEO_SNOW_FLAKE = 5;
    public const CHART_LEO_DESTIRA = 6;
    
    public function sizes()
    {
        return $this->hasMany(ClothingSize::class, 'clothing_size_chart_id');
    }
}
