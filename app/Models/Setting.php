<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const PROFILE_PICTURE_MAX_SIZE = 'profile_picture_max_size';
    public const ATHLETE_IMPORT_MAX_SIZE = 'athlete_import_max_size';
    public const FEE_HANDLING = 'fee_handling';
    public const FEE_BALANCE = 'fee_balance';
    public const FEE_CC = 'fee_cc';
    public const FEE_PAYPAL = 'fee_paypal';
    public const FEE_ACH = 'fee_ach';
    public const FEE_CHECK = 'fee_check';
    public const AUDIT_ENABLED = 'audit_enabled';
    public const WITHDRAWAL_FEES = 'withdrawal_fees';

    public const MEET_FILE_MAX_SIZE = 'meet_file_max_size';
    public const MEET_FILE_MAX_COUNT = 'meet_file_max_count';

    public const DWOLLA_UNVERIFIED_TRANSFER_CAP = 'dwolla_unverified_transfer_cap';
    public const DWOLLA_VERIFICATION_FEE = 'dwolla_verification_fee';
    public const DWOLLA_FREE_VERIFICATION_ATTEMPTS = 'dwolla_free_verification_attempts';

    public const USER_BALANCE_HOLD_DURATION = 'user_balance_hold_duration';
    public const FEATURED_MEET_FEE = 'featured_meet_fee';
    public const ENABLED_FEATURED_MEET_FEE = 'enabled_feature_meet_fee';
    public const TERMS_SERVICE_LINK = 'terms_service_link';

    public const BOOLEAN_TYPE = 'boolean';
    public const STRING_TYPE = 'string';
    public const INTEGER_TYPE = 'integer';
    public const FLOAT_TYPE = 'float';
    public const DATETIME_TYPE = 'datetime';
    public const ARRAY_TYPE = 'array';

    private const TYPES = [
        self::BOOLEAN_TYPE,
        self::STRING_TYPE,
        self::INTEGER_TYPE,
        self::FLOAT_TYPE,
        self::DATETIME_TYPE,
        self::ARRAY_TYPE,
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static $rules = [
        'fee_handling' => 'required',
        'fee_ach' => 'required',
        'fee_check' => 'required',
        'dwolla_verification_fee' => 'required',
        'fee_cc' => 'required',
        'fee_balance' => 'required',
//        'fee_paypal' => 'required',
        'dwolla_unverified_transfer_cap' => 'required',
        'dwolla_free_verification_attempts' => 'required|integer',
        'meet_file_max_count' => 'required|integer',
        'user_balance_hold_duration' => 'required|integer',
        'athlete_import_max_size' => 'required|integer',
        'meet_file_max_size' => 'required|integer',
        'profile_picture_max_size' => 'required|integer',
        'all_time_withdrawn_credit_fee' => 'required',
    ];

    public $timestamps = false;
    protected $primaryKey = 'key';

    public static function getSetting(string $key) {
        $setting = Setting::find($key);
        if ($setting == null)
            throw new \InvalidArgumentException('No such setting `' . $key . '`');

        $castedValue = $setting->value;

        if (!in_array($setting->type, self::TYPES, true))
            throw new \InvalidArgumentException('Invalid setting type `' . $setting->type . '`');

        switch($setting->type) {
            case self::DATETIME_TYPE:
                $castedValue = new \DateTime($setting->value);
                break;

            case self::ARRAY_TYPE:
                $castedValue = json_decode($setting->value, true);
                if ($castedValue === null)
                    throw new \InvalidArgumentException('JSON decode to type `' . $setting->type . '` failed.');
                break;

            default:
                if (!settype($castedValue, $setting->type))
                    throw new \InvalidArgumentException('Cast to type `' . $setting->type . '` failed.');
                break;
        }

        $setting->castedValue = $castedValue;
        return $setting;
    }

    public static function profilePictureMaxSize() {
        return self::getSetting(self::PROFILE_PICTURE_MAX_SIZE)->castedValue;
    }

    public static function athleteImportMaxSize() {
        return self::getSetting(self::ATHLETE_IMPORT_MAX_SIZE)->castedValue;
    }

    public static function feeHandling() {
        return self::getSetting(self::FEE_HANDLING)->castedValue;
    }

    public static function feeBalance() {
        return self::getSetting(self::FEE_BALANCE)->castedValue;
    }

    public static function feeCC() {
        return self::getSetting(self::FEE_CC)->castedValue;
    }

    public static function feePayPal() {
        return self::getSetting(self::FEE_PAYPAL)->castedValue;
    }

    public static function feeACH() {
        return self::getSetting(self::FEE_ACH)->castedValue;
    }

    public static function feeCheck() {
        return self::getSetting(self::FEE_CHECK)->castedValue;
    }

    public static function meetFileMaxSize() {
        return self::getSetting(self::MEET_FILE_MAX_SIZE)->castedValue;
    }

    public static function meetFileMaxCount() {
        return self::getSetting(self::MEET_FILE_MAX_COUNT)->castedValue;
    }

    public static function userBalanceHoldDuration() {
        return self::getSetting(self::USER_BALANCE_HOLD_DURATION)->castedValue;
    }

    public static function dwollaUnverifiedTransferCap() {
        return self::getSetting(self::DWOLLA_UNVERIFIED_TRANSFER_CAP)->castedValue;
    }

    public static function dwollaVerificationFee() {
        return self::getSetting(self::DWOLLA_VERIFICATION_FEE)->castedValue;
    }

    public static function dwollaFreeVerificationAttempts() {
        return self::getSetting(self::DWOLLA_FREE_VERIFICATION_ATTEMPTS)->castedValue;
    }

    public static function withdrawalFees() {
        return self::getSetting(self::WITHDRAWAL_FEES)->castedValue;
    }
}
