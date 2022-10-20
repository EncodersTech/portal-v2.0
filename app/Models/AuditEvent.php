<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\CustomAuditException;
use Illuminate\Support\Facades\Log;

class AuditEvent extends Model
{
    public const DEFAULT_USER_SYSTEM = null;

    protected $fillable = [
        'email',
        'performed_by',
        'on_behalf_of',
        'object_id',
        'param_1',
        'param_2',
        'param_3',
        'param_4',
        'param_5',
        'param_6',
        'event_meta',
    ];

    protected $casts = [
        'event_meta' => 'json',
    ];

    public static $auditEnabled = false;

    public function type()
    {
        return $this->belongsTo(AuditEventType::class, 'type_id');
    }

    private static function _audit_event(callable $callback)
    {
        try {
            if (self::$auditEnabled)
                return $callback();
            
            return false;
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                /*throw new CustomAuditException(
                    'A server error occured',
                    ErrorCodeCategory::getCategoryBase('General') + 2,
                    $e
                );
                */
                throw $e;
            } else {
                Log::critical('Audit event creation failed : ' . $e->getMessage(), [
                    'Throwable' => $e
                ]);
            }
        }
    }

    public static function attributeDiff(array $old, array $new)
    {
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $result = [];
        foreach ($keys as $key) {
            $oldValue = key_exists($key, $old) ? $old[$key] : null;
            $newValue = key_exists($key, $new) ? $new[$key] : null;

            if ($oldValue != $newValue)
                $result[$key] = ['old' => $oldValue, 'new' => $newValue];
        }

        return $result;
    }

    public static function cardLinked($card) {
        $user = auth()->user();
        return self::_audit_event(function() use ($card, $user) {
            $type = AuditEventType::find(AuditEventType::TYPE_CARD_LINK);
            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                // 'param_5' => $card->last4
            ]);
        });
    }

    public static function cardUnlinked($card) {
        $user = auth()->user();
        return self::_audit_event(function() use ($card, $user) {
            $type = AuditEventType::find(AuditEventType::TYPE_CARD_UNLINK);
            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                'param_4' => $card->id,
                'param_5' => $card->last4
            ]);
        });
    }

    public static function bankLinked($bank, User $user, \DateTime $timestamp = null) {
        if ($timestamp == null)
            $timestamp = now();

        return self::_audit_event(function() use ($bank, $user, $timestamp) {
            $type = AuditEventType::find(AuditEventType::TYPE_BANK_LINK);
            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                'param_4' => $bank->id,
                'param_5' => $bank->name,
                'created_at' => $timestamp
            ]);
        });
    }

    public static function bankVerified($bank, User $user, \DateTime $timestamp = null) {
        if ($timestamp == null)
            $timestamp = now();

        return self::_audit_event(function() use ($bank, $user, $timestamp) {
            $type = AuditEventType::find(AuditEventType::TYPE_BANK_VERIFIED);
            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                'param_4' => $bank->id,
                'param_5' => $bank->name,
                'created_at' => $timestamp
            ]);
        });
    }
    
    public static function bankUnlinked($bank, User $user, \DateTime $timestamp = null) {
        if ($timestamp == null)
            $timestamp = now();

        return self::_audit_event(function() use ($bank, $user, $timestamp) {
            $type = AuditEventType::find(AuditEventType::TYPE_BANK_UNLINK);
            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                'param_4' => $bank->id,
                'param_5' => $bank->name,
                'created_at' => $timestamp
            ]);
        });
    }

    public static function memberAdded(User $user, User $invited) {
        return self::_audit_event(function() use ($user, $invited) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEMBER_ADDED);
            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                'object_id' => $invited->id,
                'param_4' => $invited->fullName(),
                'param_5' => $invited->email
            ]);
        });
    }

    public static function memberRemoved(User $user, User $invited, bool $removedSelf = false) {
        return self::_audit_event(function() use ($user, $invited, $removedSelf) {
            $type = AuditEventType::find(
                $removedSelf ? AuditEventType::TYPE_REMOVED_SELF_FROM_ACCOUNT : AuditEventType::TYPE_MEMBER_REMOVED
            );
            return $type->events()->create([
                'performed_by' => ($removedSelf ? $invited->id : $user->id),
                'on_behalf_of' => $user->id,
                'object_id' => $invited->id,
                'param_4' => $invited->fullName(),
                'param_5' => $invited->email
            ]);
        });
    }

    public static function memberPermissionChanged(User $user, User $invited, array $change) {
        return self::_audit_event(function() use ($user, $invited, $change) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEMBER_PERMISSIONS_CHANGED);
            /*
            $meta = json_encode($change);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */

            return $type->events()->create([
                'performed_by' => $user->id,
                'on_behalf_of' => $user->id,
                'object_id' => $invited->id,
                'param_4' => $invited->fullName(),
                'param_5' => $invited->email,
                'event_meta' => $change
            ]);
        });
    }

    public static function gymCreated(User $user, User $performer, Gym $gym) {
        return self::_audit_event(function() use ($user, $performer, $gym) {
            $type = AuditEventType::find(AuditEventType::TYPE_GYM_CREATED);
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $gym->id,
                'param_4' => $gym->name,
            ]);
        });
    }

    public static function gymArchivalStatusChanged(User $user, User $performer, Gym $gym, bool $archived = true) {
        return self::_audit_event(function() use ($user, $performer, $gym, $archived) {
            $type = AuditEventType::find($archived ? AuditEventType::TYPE_GYM_ARCHIVED : AuditEventType::TYPE_GYM_RESTORED);
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $gym->id,
                'param_4' => $gym->name,
            ]);
        });
    }

    public static function gymUpdated(User $user, User $performer, Gym $gym, array $change) {
        return self::_audit_event(function() use ($user, $performer, $gym, $change) {
            $type = AuditEventType::find(AuditEventType::TYPE_GYM_UPDATED);
            /*
            $meta = json_encode($change);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $gym->id,
                'param_4' => $gym->name,
                'event_meta' => $change
            ]);
        });
    }

    public static function athleteCreated(User $user, User $performer, Athlete $athlete) {
        return self::_audit_event(function() use ($user, $performer, $athlete) {
            $type = AuditEventType::find(AuditEventType::TYPE_ATHLETE_CREATED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $athlete->id,
                'param_1' => $athlete->gym->id,
                'param_4' => $athlete->fullName(),
                'param_5' => $athlete->gym->name
            ]);
        });
    }

    public static function athleteRemoved(User $user, User $performer, Athlete $athlete) {
        return self::_audit_event(function() use ($user, $performer, $athlete) {
            $type = AuditEventType::find(AuditEventType::TYPE_ATHLETE_OVERWRITTEN);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $athlete->id,
                'param_1' => $athlete->gym->id,
                'param_4' => $athlete->fullName(),
                'param_5' => $athlete->gym->name
            ]);
        });
    }

    public static function athleteUpdated(User $user, User $performer, Athlete $athlete, array $change) {
        return self::_audit_event(function() use ($user, $performer, $change, $athlete) {
            $type = AuditEventType::find(AuditEventType::TYPE_ATHLETE_UPDATED);
            /*
            $meta = json_encode($change);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $athlete->id,
                'param_1' => $athlete->gym->id,
                'param_4' => $athlete->fullName(),
                'param_5' => $athlete->gym->name,
                'event_meta' => $change
            ]);
        });
    }

    public static function athleteImportedCsv(User $user, User $performer, Athlete $athlete, int $body,
        array $raw, bool $overwrite = false) {
        return self::_audit_event(function() use ($user, $performer, $athlete, $body, $raw, $overwrite) {
            $type = AuditEventType::find(
                $overwrite ? 
                AuditEventType::TYPE_ATHLETE_OVERWRITTEN :
                AuditEventType::TYPE_ATHLETE_IMPORTED
            );
            
            /*
            $meta = json_encode($raw);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $athlete->id,
                'param_1' => $athlete->gym->id,
                'param_2' => Gym::IMPORT_METHOD_CSV,
                'param_3' => $body,
                'param_4' => $athlete->fullName(),
                'param_5' => $athlete->gym->name,
                'event_meta' => $raw
            ]);
        });
    }

    public static function athleteImportedApi(User $user, User $performer, Athlete $athlete, int $body,
        array $raw, bool $overwrite = false) {
        return self::_audit_event(function() use ($user, $performer, $athlete, $body, $raw, $overwrite) {
            $type = AuditEventType::find(
                $overwrite ? 
                AuditEventType::TYPE_ATHLETE_OVERWRITTEN :
                AuditEventType::TYPE_ATHLETE_IMPORTED
            );

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $athlete->id,
                'param_1' => $athlete->gym->id,
                'param_2' => Gym::IMPORT_METHOD_API,
                'param_3' => $body,
                'param_4' => $athlete->fullName(),
                'param_5' => $athlete->gym->name,
                'event_meta' => $raw
            ]);
        });
    }

    public static function coachCreated(User $user, User $performer, Coach $coach) {
        return self::_audit_event(function() use ($user, $performer, $coach) {
            $type = AuditEventType::find(AuditEventType::TYPE_COACH_CREATED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $coach->id,
                'param_1' => $coach->gym->id,
                'param_4' => $coach->fullName(),
                'param_5' => $coach->gym->name
            ]);
        });
    }

    public static function coachRemoved(User $user, User $performer, Coach $coach) {
        return self::_audit_event(function() use ($user, $performer, $coach) {
            $type = AuditEventType::find(AuditEventType::TYPE_COACH_DELETED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $coach->id,
                'param_1' => $coach->gym->id,
                'param_4' => $coach->fullName(),
                'param_5' => $coach->gym->name
            ]);
        });
    }

    public static function coachUpdated(User $user, User $performer, Coach $coach, array $change) {
        return self::_audit_event(function() use ($user, $performer, $change, $coach) {
            $type = AuditEventType::find(AuditEventType::TYPE_COACH_UPDATED);
            /*
            $meta = json_encode($change);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $coach->id,
                'param_1' => $coach->gym->id,
                'param_4' => $coach->fullName(),
                'param_5' => $coach->gym->name,
                'event_meta' => $change
            ]);
        });
    }

    public static function coachImportedCsv(User $user, User $performer, Coach $coach, int $body,
        array $raw, bool $overwrite = false) {
        return self::_audit_event(function() use ($user, $performer, $coach, $body, $raw, $overwrite) {
            $type = AuditEventType::find(
                $overwrite ? 
                AuditEventType::TYPE_ATHLETE_OVERWRITTEN :
                AuditEventType::TYPE_ATHLETE_IMPORTED
            );
            
            /*
            $meta = json_encode($raw);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $coach->id,
                'param_1' => $coach->gym->id,
                'param_2' => Gym::IMPORT_METHOD_CSV,
                'param_3' => $body,
                'param_4' => $coach->fullName(),
                'param_5' => $coach->gym->name,
                'event_meta' => $raw
            ]);
        });
    }

    public static function meetCreated(User $user, User $performer, Meet $meet) {
        return self::_audit_event(function() use ($user, $performer, $meet) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_CREATED);
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $meet->id,
                'param_1' => $meet->gym->id,
                'param_4' => $meet->name,
                'param_5' => $meet->gym->name,
            ]);
        });
    }

    public static function meetUpdated(User $user, User $performer, Meet $meet, array $change) {
        return self::_audit_event(function() use ($user, $performer, $meet, $change) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_UPDATED);
            /*
            $meta = json_encode($change);
            if ($meta === false)
                throw new CustomBaseException('Server error', ErrorCodeCategory::getCategoryBase('General') + 1);
            */

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $meet->id,
                'param_1' => $meet->gym->id,
                'param_4' => $meet->name,
                'param_5' => $meet->gym->name,
                'event_meta' => $change
            ]);
        });
    }

    public static function meetArchivalStatusChanged(User $user, User $performer, Meet $meet, bool $archived = true) {
        return self::_audit_event(function() use ($user, $performer, $meet, $archived) {
            $type = AuditEventType::find($archived ? AuditEventType::TYPE_MEET_ARCHIVED : AuditEventType::TYPE_MEET_RESTORED);
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $meet->id,
                'param_1' => $meet->gym->id,
                'param_4' => $meet->name,
                'param_5' => $meet->gym->name,
            ]);
        });
    }

    public static function meetRemoved(User $user, User $performer, Meet $meet) {
        return self::_audit_event(function() use ($user, $performer, $meet) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_DELETED);
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $meet->id,
                'param_1' => $meet->gym->id,
                'param_4' => $meet->name,
                'param_5' => $meet->gym->name,
            ]);
        });
    }

    public static function meetPublishingStatusChanged(User $user, User $performer, Meet $meet, bool $published = true) {
        return self::_audit_event(function() use ($user, $performer, $meet, $published) {
            $type = AuditEventType::find($published ? AuditEventType::TYPE_MEET_PUBLISHED : AuditEventType::TYPE_MEET_UNPUBLISHED);
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $meet->id,
                'param_1' => $meet->gym->id,
                'param_4' => $meet->name,
                'param_5' => $meet->gym->name,
            ]);
        });
    }

    public static function registrationCreated(User $user, User $performer, MeetRegistration $registration, array $details) {
        return self::_audit_event(function() use ($user, $performer, $registration, $details) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_CREATED);

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $registration->id,
                'param_1' => $registration->gym->id,
                'param_2' => $registration->meet->id,
                'param_4' => $registration->gym->name,
                'param_5' => $registration->meet->name,
                'event_meta' => $details
            ]);
        });
    }

    public static function registrationUpdated(User $user, User $performer, MeetRegistration $registration, array $change) {
        return self::_audit_event(function() use ($user, $performer, $registration, $change) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_UPDATED);

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $registration->id,
                'param_1' => $registration->gym->id,
                'param_2' => $registration->meet->id,
                'param_4' => $registration->gym->name,
                'param_5' => $registration->meet->name,
                'event_meta' => $change
            ]);
        });
    }

    public static function checkAccepted(User $user, User $performer, MeetTransaction $check,
        CheckConfirmationTransaction $confirmation) {
        return self::_audit_event(function() use ($user, $performer, $check, $confirmation) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_CHECK_ACCEPTED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $check->id,
                'param_1' => $check->meet_registration->gym->id,
                'param_2' => $check->meet_registration->meet->id,
                'param_3' => $confirmation->id,                
                'param_4' => $check->meet_registration->gym->name,
                'param_5' => $check->meet_registration->meet->name,
            ]);
        });
    }

    public static function checkRejected(User $user, User $performer, MeetTransaction $check) {
        return self::_audit_event(function() use ($user, $performer, $check) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_CHECK_REJECTED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $check->id,
                'param_1' => $check->meet_registration->gym->id,
                'param_2' => $check->meet_registration->meet->id,                
                'param_4' => $check->meet_registration->gym->name,
                'param_5' => $check->meet_registration->meet->name,
            ]);
        });
    }

    public static function registrationTransactionPaid(User $user, User $performer, MeetTransaction $transaction) {
        return self::_audit_event(function() use ($user, $performer, $transaction) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_TRANSACTION_PAID);

            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $transaction->id,
                'param_1' => $transaction->meet_registration->gym->id,
                'param_2' => $transaction->meet_registration->meet->id,                
                'param_4' => $transaction->meet_registration->gym->name,
                'param_5' => $transaction->meet_registration->meet->name,
            ]);
        });
    }

    public static function waitlistConfirmed(User $user, User $performer,
        MeetTransaction $transaction) {
        return self::_audit_event(function() use ($user, $performer, $transaction) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_WAITLIST_ACCEPTED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'object_id' => $transaction->id,
                'param_1' => $transaction->meet_registration->gym->id,
                'param_2' => $transaction->meet_registration->meet->id,
                'param_3' => $transaction->meet_registration->id,
                'param_4' => $transaction->meet_registration->gym->name,
                'param_5' => $transaction->meet_registration->meet->name,
            ]);
        });
    }

    public static function waitlistRejected(User $user, User $performer,
        MeetTransaction $transaction) {
        return self::_audit_event(function() use ($user, $performer, $transaction) {
            $type = AuditEventType::find(AuditEventType::TYPE_REGISTRATION_WAITLIST_REJECTED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'param_1' => $transaction->meet_registration->gym->id,
                'param_2' => $transaction->meet_registration->meet->id,
                'param_3' => $transaction->meet_registration->id,
                'param_4' => $transaction->meet_registration->gym->name,
                'param_5' => $transaction->meet_registration->meet->name,
            ]);
        });
    }

    public static function usagSanctionReceived(USAGSanction $sanction) {
        return self::_audit_event(function() use ($sanction) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_USAG_SANCTION_RECEIVED);
            
            return $type->events()->create([
                'on_behalf_of' => ($sanction->gym !== null ? $sanction->gym->user->id : self::DEFAULT_USER_SYSTEM),
                'param_1' => $sanction->id,
                'param_2' => ($sanction->gym !== null ? $sanction->gym->id : null),
                'param_3' => $sanction->action,
                'param_4' => $sanction->number,
                'event_meta' => $sanction->payload
            ]);
        });
    }

    public static function usagSanctionProcessed(User $user, User $performer, USAGSanction $sanction) {
        return self::_audit_event(function() use ($user, $performer, $sanction) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_USAG_SANCTION_PROCESSED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'param_1' => $sanction->id,
                'param_2' => ($sanction->gym !== null ? $sanction->gym->id : null),
                'param_3' => $sanction->action,
                'param_4' => $sanction->number,
                'event_meta' => $sanction->payload
            ]);
        });
    }

    public static function usagReservationReceived(USAGReservation $reservation) {
        return self::_audit_event(function() use ($reservation) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_USAG_RESERVATION_RECEIVED);
            
            return $type->events()->create([
                'on_behalf_of' => ($reservation->gym !== null ? $reservation->gym->user->id : self::DEFAULT_USER_SYSTEM),
                'param_1' => $reservation->id,
                'param_2' => ($reservation->gym !== null ? $reservation->gym->id : null),
                'param_3' => $reservation->action,
                'param_4' => $reservation->usag_sanction_id,
                'event_meta' => $reservation->payload
            ]);
        });
    }

    public static function usagReservationProcessed(User $user, User $performer, USAGReservation $reservation) {
        return self::_audit_event(function() use ($user, $performer, $reservation) {
            $type = AuditEventType::find(AuditEventType::TYPE_MEET_USAG_RESERVATION_PROCESSED);
            
            return $type->events()->create([
                'performed_by' => $performer->id,
                'on_behalf_of' => $user->id,
                'param_1' => $reservation->id,
                'param_2' => ($reservation->gym !== null ? $reservation->gym->id : null),
                'param_3' => $reservation->action,
                'param_4' => $reservation->usag_sanction_id,
                'event_meta' => $reservation->payload
            ]);
        });
    }
}
