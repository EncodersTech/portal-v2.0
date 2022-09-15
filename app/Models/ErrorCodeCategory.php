<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorCodeCategory extends Model
{

    protected static $categoryBases = [
        'General' => 1000,
        'Stripe' => 2000,
        'Dwolla' => 3000,
        'PayPal' => 4000
    ];

    public static function getCategoryBases()
    {
        return self::$categoryBases;
    }

    public static function getCategoryBase(string $categoryName) : int
    {
        return self::$categoryBases[$categoryName];
    }

    public function errorCodes()
    {
        return $this->hasMany(ErrorCode::class);
    }
}
