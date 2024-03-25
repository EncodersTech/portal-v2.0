<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MassMailer extends Model
{
    protected $table = 'massmailer';
    protected $fillable = ['host', 'meet_id', 'registered_gyms', 'subject', 'message', 'attachments'];
}
