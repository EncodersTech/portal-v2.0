<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedCoachImport extends Model
{
    public const ERROR_CODE_DUPLICATE = -9999;
    public const ERROR_CODE_VALIDATION = -9998;
    public const ERROR_CODE_SERVER = -9997;

    protected $guarded = ['id'];

    protected $dates = ['dob'];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }
}
