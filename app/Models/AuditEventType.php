<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditEventType extends Model
{
    public $guarded = [];
    
    public const TYPE_CARD_LINK = 1;
    public const TYPE_CARD_UNLINK = 2;
    public const TYPE_BANK_LINK = 3;
    public const TYPE_BANK_VERIFIED = 4;
    public const TYPE_BANK_UNLINK = 5;
    public const TYPE_WITHDRAWAL_REQUESTED = 6;

    public const TYPE_MEMBER_ADDED = 101;
    public const TYPE_MEMBER_PERMISSIONS_CHANGED = 102;
    public const TYPE_MEMBER_REMOVED = 103;
    public const TYPE_REMOVED_SELF_FROM_ACCOUNT = 104;

    public const TYPE_GYM_CREATED = 201;
    public const TYPE_GYM_UPDATED = 202;
    public const TYPE_GYM_ARCHIVED = 203;
    public const TYPE_GYM_RESTORED = 204;

    public const TYPE_ATHLETE_CREATED = 301;
    public const TYPE_ATHLETE_UPDATED = 302;
    public const TYPE_ATHLETE_DELETED = 303;
    public const TYPE_ATHLETE_IMPORTED = 304;
    public const TYPE_ATHLETE_OVERWRITTEN = 305;
    public const TYPE_COACH_CREATED = 306;
    public const TYPE_COACH_UPDATED = 307;
    public const TYPE_COACH_DELETED = 308;
    public const TYPE_COACH_IMPORTED = 310;
    public const TYPE_COACH_OVERWRITTEN = 311;

    public const TYPE_MEET_CREATED = 401;
    public const TYPE_MEET_UPDATED = 402;
    public const TYPE_MEET_DELETED = 403;
    public const TYPE_MEET_ARCHIVED = 404;
    public const TYPE_MEET_RESTORED = 405;
    public const TYPE_MEET_PUBLISHED = 406;
    public const TYPE_MEET_UNPUBLISHED = 407;
    public const TYPE_MEET_USAG_SANCTION_RECEIVED = 421;
    public const TYPE_MEET_USAG_SANCTION_PROCESSED = 422;
    public const TYPE_MEET_USAG_SANCTION_DISMISSED = 423;

    public const TYPE_REGISTRATION_CREATED = 501;
    public const TYPE_REGISTRATION_UPDATED = 502;
    public const TYPE_REGISTRATION_WAITLIST_ACCEPTED = 503;
    public const TYPE_REGISTRATION_WAITLIST_REJECTED = 504;
    public const TYPE_REGISTRATION_CHECK_ACCEPTED = 505;
    public const TYPE_REGISTRATION_CHECK_REJECTED = 506;
    public const TYPE_REGISTRATION_TRANSACTION_PAID = 507;
    public const TYPE_MEET_USAG_RESERVATION_RECEIVED = 521;
    public const TYPE_MEET_USAG_RESERVATION_PROCESSED = 522;
    public const TYPE_MEET_USAG_RESERVATION_DISMISSED = 523;

    public function category()
    {
        return $this->belongsTo(AuditEventCategory::class, 'category_id');
    }

    public function events()
    {
        return $this->hasMany(AuditEvent::class, 'type_id');
    }
}
